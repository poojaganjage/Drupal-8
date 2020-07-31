<?php

namespace Drupal\aws_cloud\Service\Ec2;

use Aws\Api\DateTimeResult;
use Aws\Credentials\AssumeRoleCredentialProvider;
use Aws\Credentials\CredentialProvider;
use Aws\Ec2\Ec2Client;
use Aws\Ec2\Exception\Ec2Exception;
use Aws\Endpoint\PartitionEndpointProvider;
use Aws\MockHandler;
use Aws\Result;
use Aws\Sts\StsClient;
use Drupal\aws_cloud\Entity\Ec2\Instance;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Service\CloudServiceBase;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Session\AccountInterface;

/**
 * Ec2Service interacts with the Amazon EC2 API.
 */
class Ec2Service extends CloudServiceBase implements Ec2ServiceInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Cloud context string.
   *
   * @var string
   */
  protected $cloudContext;

  /**
   * The config factory.
   *
   * Subclasses should use the self::config() method, which may be overridden to
   * address specific needs when loading config, rather than this property
   * directly. See \Drupal\Core\Form\ConfigFormBase::config() for an example of
   * this.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Field type plugin manager.
   *
   * @var \Drupal\core\Field\FieldTypePluginManagerInterface
   */
  protected $fieldTypePluginManager;

  /**
   * Entity field manager interface.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * TRUE to run the operation.  FALSE to run the operation in validation mode.
   *
   * @var bool
   */
  private $dryRun;

  /**
   * The lock interface.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * The queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * Constructs a new Ec2Service object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   An entity type manager instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   * @param \Drupal\core\Field\FieldTypePluginManagerInterface $field_type_plugin_manager
   *   The field type plugin manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock interface.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory,
                              AccountInterface $current_user,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
                              FieldTypePluginManagerInterface $field_type_plugin_manager,
                              EntityFieldManagerInterface $entity_field_manager,
                              LockBackendInterface $lock,
                              QueueFactory $queue_factory) {

    // The parent constructor takes care of $this->messenger object.
    parent::__construct();

    // Setup the entity type manager for querying entities.
    $this->entityTypeManager = $entity_type_manager;

    // Setup the configuration factory.
    $this->configFactory = $config_factory;

    // Setup the dryRun flag.
    $this->dryRun = (bool) $this->configFactory->get('aws_cloud.settings')->get('aws_cloud_test_mode');

    $this->currentUser = $current_user;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
    $this->fieldTypePluginManager = $field_type_plugin_manager;

    $this->entityFieldManager = $entity_field_manager;
    $this->lock = $lock;
    $this->queueFactory = $queue_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function setCloudContext($cloud_context) {
    $this->cloudContext = $cloud_context;
    $this->cloudConfigPluginManager->setCloudContext($cloud_context);
  }

  /**
   * Load and return an Ec2Client.
   *
   * @param array $credentials
   *   The array of credentials.
   *
   * @return \Aws\Ec2\Ec2Client|null
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  protected function getEc2Client(array $credentials = []) {

    if (empty($credentials)) {
      $credentials = $this->cloudConfigPluginManager->loadCredentials();
    }

    try {
      $ec2_params = [
        'region' => $credentials['region'],
        'version' => $credentials['version'],
      ];
      $provider = FALSE;

      // Load credentials if needed.
      if (empty($credentials['use_instance_profile'])) {
        if (!empty($credentials['env'])) {
          $this->setCredentialsToEnv(
            $credentials['env']['access_key'],
            $credentials['env']['secret_key']
          );
          $provider = CredentialProvider::env();
        }
        else {
          $provider = CredentialProvider::ini('default', $credentials['ini_file']);
        }
        $provider = CredentialProvider::memoize($provider);
      }

      if (!empty($credentials['use_assume_role'])) {
        $sts_params = [
          'region' => $credentials['region'],
          'version' => $credentials['version'],
        ];
        if ($provider !== FALSE) {
          $sts_params['credentials'] = $provider;
        }
        $assumeRoleCredentials = new AssumeRoleCredentialProvider([
          'client' => new StsClient($sts_params),
          'assume_role_params' => [
            'RoleArn' => $credentials['role_arn'],
            'RoleSessionName' => 'ec2_client_assume_role',
          ],
        ]);
        // Memoize takes care of re-authenticating when the tokens expire.
        $assumeRoleCredentials = CredentialProvider::memoize($assumeRoleCredentials);
        $ec2_params['credentials'] = $assumeRoleCredentials;

        // If switch role is enabled, execute one more assume role.
        if (!empty($credentials['use_switch_role'])) {
          $switch_sts_params = [
            'region' => $credentials['region'],
            'version' => $credentials['version'],
            'credentials' => $assumeRoleCredentials,
          ];
          $switchRoleCredentials = new AssumeRoleCredentialProvider([
            'client' => new StsClient($switch_sts_params),
            'assume_role_params' => [
              'RoleArn' => $credentials['switch_role_arn'],
              'RoleSessionName' => 'ec2_client_switch_role',
            ],
          ]);
          $switchRoleCredentials = CredentialProvider::memoize($switchRoleCredentials);
          $ec2_params['credentials'] = $switchRoleCredentials;
        }
      }
      elseif ($provider !== FALSE) {
        $ec2_params['credentials'] = $provider;
      }

      $ec2_client = new Ec2Client($ec2_params);
    }
    catch (\Exception $e) {
      $ec2_client = NULL;
      $this->logger('aws_cloud')->error($e->getMessage());
    }
    $this->addMockHandler($ec2_client);
    return $ec2_client;
  }

  /**
   * Set credentials to ENV.
   *
   * @param string $access_key
   *   The access key.
   * @param string $secret_key
   *   The secret key.
   */
  protected function setCredentialsToEnv($access_key, $secret_key) {
    putenv(CredentialProvider::ENV_KEY . "=$access_key");
    putenv(CredentialProvider::ENV_SECRET . "=$secret_key");
  }

  /**
   * Add a mock handler of AWS SDK for testing.
   *
   * The mock data of AWS response is saved
   * in configuration "aws_cloud_mock_data".
   *
   * @param \Aws\Ec2\Ec2Client $ec2_client
   *   The ec2 client.
   *
   * @see https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_handlers-and-middleware.html
   */
  private function addMockHandler(Ec2Client $ec2_client) {
    $mock_data = $this->configFactory->get('aws_cloud.settings')->get('aws_cloud_mock_data');
    if ($this->dryRun && $mock_data) {
      $func = static function ($command, $request) {
        $mock_data = \Drupal::service('config.factory')
          ->get('aws_cloud.settings')
          ->get('aws_cloud_mock_data');
        $mock_data = json_decode($mock_data, TRUE);

        // If the mock data of a command is defined.
        $command_name = $command->getName();
        if (isset($mock_data[$command_name])) {
          $result_data = $mock_data[$command_name];

          // Because launch time is special,
          // we need to convert it from string to DateTimeResult.
          if ($command_name === 'DescribeInstances') {
            // NOTE:  We wont' use foreach ($items ?: [] $item) here since
            // $result_data['Reservations'] is used as a reference.
            foreach ($result_data['Reservations'] as &$reservation) {
              foreach ($reservation['Instances'] as &$instance) {
                // Initialize $instance['LaunchTime'].
                $instance['LaunchTime'] = !empty($instance['LaunchTime'])
                  ? new DateTimeResult($instance['LaunchTime'])
                  : new DateTimeResult(0);
              }
            }
          }

          return new Result($result_data);
        }

        if ($command_name === 'DescribeAccountAttributes') {
          // Return an empty array so testing doesn't error out.
          $result_data = [
            'AccountAttributes' => [
              [
                'AttributeName' => 'supported-platforms',
                'AttributeValues' => [
                  [
                    'AttributeValue' => 'VPC',
                  ],
                ],
              ],
            ],
          ];
          return new Result($result_data);
        }

        return new Result();
      };

      // Set mock handler.
      $ec2_client->getHandlerList()->setHandler(new MockHandler([$func, $func]));
    }
  }

  /**
   * Execute the API of Amazon EC2 Service.
   *
   * @param string $operation
   *   The operation to perform.
   * @param array $params
   *   An array of parameters.
   * @param array $credentials
   *   The array of credentials.
   *
   * @return array
   *   Array of execution result or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  private function execute($operation, array $params = [], array $credentials = []) {
    $results = NULL;

    $ec2_client = $this->getEc2Client($credentials);
    if (empty($ec2_client)) {
      throw new Ec2ServiceException('No EC2 Client found.  Cannot perform API operations');
    }

    try {
      // Let other modules alter the parameters
      // before they are sent through the API.
      \Drupal::moduleHandler()->invokeAll('aws_cloud_pre_execute_alter', [
        &$params,
        $operation,
        $this->cloudContext,
      ]);

      $command = $ec2_client->getCommand($operation, $params);
      $results = $ec2_client->execute($command);

      // Let other modules alter the results before the module processes it.
      \Drupal::moduleHandler()->invokeAll('aws_cloud_post_execute_alter', [
        &$results,
        $operation,
        $this->cloudContext,
      ]);
    }
    catch (Ec2Exception $e) {
      $this->messenger->addError($this->t('Error: The operation "@operation" could not be performed.', [
        '@operation' => $operation,
      ]));

      $this->messenger->addError($this->t('Error Info: @error_info', [
        '@error_info' => $e->getAwsErrorCode(),
      ]));

      $this->messenger->addError($this->t('Error from: @error_type-side', [
        '@error_type' => $e->getAwsErrorType(),
      ]));

      $this->messenger->addError($this->t('Status Code: @status_code', [
        '@status_code' => $e->getStatusCode(),
      ]));

      $this->messenger->addError($this->t('Message: @msg', ['@msg' => $e->getAwsErrorMessage()]));

    }
    catch (\Exception $e) {
      $this->handleException($e);
    }
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function associateAddress(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('AssociateAddress', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function allocateAddress(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('AllocateAddress', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function associateIamInstanceProfile(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('AssociateIamInstanceProfile', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeAccountAttributes(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeAccountAttributes', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function disassociateIamInstanceProfile(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DisassociateIamInstanceProfile', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function replaceIamInstanceProfileAssociation(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('ReplaceIamInstanceProfileAssociation', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeIamInstanceProfileAssociations(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeIamInstanceProfileAssociations', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function authorizeSecurityGroupIngress(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('AuthorizeSecurityGroupIngress', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function authorizeSecurityGroupEgress(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('AuthorizeSecurityGroupEgress', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function createImage(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('CreateImage', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function modifyImageAttribute(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('ModifyImageAttribute', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function createKeyPair(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('CreateKeyPair', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function createNetworkInterface(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('CreateNetworkInterface', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function modifyNetworkInterfaceAttribute(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('ModifyNetworkInterfaceAttribute', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function createTags(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('CreateTags', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteTags(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DeleteTags', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function createVolume(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('CreateVolume', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function modifyVolume(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('ModifyVolume', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function createSnapshot(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('CreateSnapshot', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function createVpc(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('CreateVpc', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function createFlowLogs(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('CreateFlowLogs', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function createVpcPeeringConnection(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('CreateVpcPeeringConnection', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function acceptVpcPeeringConnection(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('AcceptVpcPeeringConnection', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeVpcPeeringConnections(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeVpcPeeringConnections', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeFlowLogs(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeFlowLogs', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function createSecurityGroup(array $params = []) {
    $params += $this->getDefaultParameters();
    $results = $this->execute('CreateSecurityGroup', $params);
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function deregisterImage(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DeregisterImage', $params);
  }

  /**
   * Get Regions list from API.
   *
   * @param array $params
   *   The array of API parameters.
   * @param array $credentials
   *   The array of credentials.
   *
   * @return array
   *   The array of API result.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeInstances(array $params = [], array $credentials = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeInstances', $params, $credentials);
  }

  /**
   * {@inheritdoc}
   */
  public function describeInstanceAttribute(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeInstanceAttribute', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeImages(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeImages', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeImageAttribute(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeImageAttribute', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeSecurityGroups(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeSecurityGroups', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeNetworkInterfaces(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeNetworkInterfaces', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeAddresses(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeAddresses', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeSnapshots(array $params = []) {
    $params += $this->getDefaultParameters();
    $cloud_config = $this->cloudConfigPluginManager->loadConfigEntity();
    $account_id = $cloud_config->get('field_account_id')->value;
    if ($cloud_config->hasField('field_use_assume_role')) {
      $use_assume_role = $cloud_config->get('field_use_assume_role')->value ?? FALSE;
    }
    if ($cloud_config->hasField('field_use_switch_role')) {
      $use_switch_role = $cloud_config->get('field_use_switch_role')->value ?? FALSE;
    }
    if (!empty($use_assume_role) && !empty($use_switch_role)) {
      $account_id = trim($cloud_config->get('field_switch_role_account_id')->value);
    }
    $params['RestorableByUserIds'] = [$account_id];
    return $this->execute('DescribeSnapshots', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeKeyPairs(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeKeyPairs', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeVolumes(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeVolumes', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeAvailabilityZones(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeAvailabilityZones', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeVpcs(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeVpcs', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function associateVpcCidrBlock(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('AssociateVpcCidrBlock', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function disassociateVpcCidrBlock(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DisassociateVpcCidrBlock', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeSubnets(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeSubnets', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function createSubnet(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('CreateSubnet', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function getRegions() {
    $regions = PartitionEndpointProvider::defaultProvider()
      ->getPartition($region = '', 'ec2')['regions'];

    foreach ($regions ?: [] as $region => $region_name) {
      $item[$region] = $region_name['description'];
    }

    return $item;
  }

  /**
   * {@inheritdoc}
   */
  public function getEndpointUrls() {
    // The $endpoints will be an array like ['us-east-1' => [], 'us-east-2' =>
    // [], ...].
    $endpoints = PartitionEndpointProvider::defaultProvider()
      ->getPartition('', 'ec2')['services']['ec2']['endpoints'];

    $urls = [];
    foreach ($endpoints ?: [] as $endpoint => $item) {
      $url = "https://ec2.$endpoint.amazonaws.com";
      $urls[$endpoint] = $url;
    }

    return $urls;
  }

  /**
   * {@inheritdoc}
   */
  public function importKeyPair(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('ImportKeyPair', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function terminateInstance(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('TerminateInstances', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteSecurityGroup(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DeleteSecurityGroup', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteNetworkInterface(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DeleteNetworkInterface', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function disassociateAddress(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DisassociateAddress', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function releaseAddress(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('ReleaseAddress', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteKeyPair(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DeleteKeyPair', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteVolume(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DeleteVolume', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function attachVolume(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('AttachVolume', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function detachVolume(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DetachVolume', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteSnapshot(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DeleteSnapshot', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteVpc(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DeleteVpc', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteVpcPeeringConnection(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DeleteVpcPeeringConnection', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteFlowLogs(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DeleteFlowLogs', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteSubnet(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DeleteSubnet', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function revokeSecurityGroupIngress(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('RevokeSecurityGroupIngress', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function revokeSecurityGroupEgress(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('RevokeSecurityGroupEgress', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function runInstances(array $params = [], array $tags = []) {
    $params += $this->getDefaultParameters();

    // Add meta tags to identify where the instance was launched from.
    $params['TagSpecifications'] = [
      [
        'ResourceType' => 'instance',
        'Tags' => [
          [
            'Key' => 'aws_cloud_' . Instance::TAG_LAUNCH_ORIGIN,
            'Value' => \Drupal::request()->getHost(),
          ],
          [
            'Key' => 'aws_cloud_' . Instance::TAG_LAUNCH_SOFTWARE,
            'Value' => 'Drupal 8 Cloud Orchestrator',
          ],
          [
            'Key' => 'aws_cloud_' . Instance::TAG_LAUNCHED_BY,
            'Value' => $this->currentUser->getAccountName(),
          ],
          [
            'Key' => 'aws_cloud_' . Instance::TAG_LAUNCHED_BY_UID,
            'Value' => $this->currentUser->id(),
          ],
        ],
      ],
    ];

    // If there are tags, add them to the Tags array.
    foreach ($tags ?: [] as $tag) {
      $params['TagSpecifications'][0]['Tags'][] = $tag;
    }

    return $this->execute('RunInstances', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function stopInstances(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('StopInstances', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function startInstances(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('StartInstances', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function modifyInstanceAttribute(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('ModifyInstanceAttribute', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function rebootInstances(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('RebootInstances', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function createLaunchTemplate(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('CreateLaunchTemplate', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function modifyLaunchTemplate(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('ModifyLaunchTemplate', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteLaunchTemplate(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DeleteLaunchTemplate', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeLaunchTemplates(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeLaunchTemplates', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function createLaunchTemplateVersion(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('CreateLaunchTemplateVersion', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteLaunchTemplateVersions(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DeleteLaunchTemplateVersions', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeLaunchTemplateVersions(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('DescribeLaunchTemplateVersions', $params);
  }

  /**
   * Call the API for updated entities and store them as Instance entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   * @param string $entity_type
   *   Entity type string.
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateInstanceEntities(array $params = [], $clear = TRUE, $entity_type = '', $cloud_context = '') {
    $updated = FALSE;
    // Call the API and get all instances.
    $result = $this->describeInstances($params);

    if (isset($result)) {

      $all_instances = $this->loadAllEntities($entity_type);
      $stale = [];
      // Make it easier to lookup the instances by setting up
      // the array with the instance_id.
      foreach ($all_instances ?: [] as $instance) {
        $stale[$instance->getInstanceId()] = $instance;
      }

      /* @var \Drupal\Core\Batch\BatchBuilder $batch_builder */
      $batch_builder = $this->initBatch('Instance Update');

      // Loop through the reservations and store each one as an Instance entity.
      foreach ($result['Reservations'] ?: [] as $reservation) {

        foreach ($reservation['Instances'] ?: [] as $instance) {
          // Keep track of instances that do not exist anymore
          // delete them after saving the rest of the instances.
          if (isset($stale[$instance['InstanceId']])) {
            unset($stale[$instance['InstanceId']]);
          }
          // Store the Reservation OwnerId in instance so batch
          // callback has access.
          $instance['reservation_ownerid'] = $reservation['OwnerId'];
          $instance['reservation_id'] = $reservation['ReservationId'];

          $batch_builder->addOperation([
            Ec2BatchOperations::class,
            'updateInstance',
          ], [$cloud_context, $instance]);
        }
      }

      $batch_builder->addOperation([
        Ec2BatchOperations::class,
        'finished',
      ], [$entity_type, $stale, $clear]);
      $this->runBatch($batch_builder);
      $updated = TRUE;
    }
    return $updated;
  }

  /**
   * Update the EC2Instances.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateInstances(array $params = [], $clear = TRUE) {
    $entity_type = 'aws_cloud_instance';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $updated = $this->updateInstanceEntities($params, $clear, $entity_type, $this->cloudContext);

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * {@inheritdoc}
   */
  public function updateInstancesWithoutBatch(array $params = [], $clear = FALSE) {
    $updated = FALSE;
    $entity_type = 'aws_cloud_instance';
    $lock_name = $this->getLockKey($entity_type);
    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    // Load all entities by cloud_context.
    $instance_entities = $this->entityTypeManager
      ->getStorage($entity_type)
      ->loadByProperties([
        'cloud_context' => $this->cloudContext,
      ]);

    $result = $this->describeInstances($params);

    if (isset($result)) {

      $stale = [];
      // Make it easier to lookup the images by setting up
      // the array with the image_id.
      foreach ($instance_entities ?: [] as $instance) {
        $stale[$instance->getInstanceId()] = $instance;
      }

      foreach ($result['Reservations'] ?: [] as $reservation) {

        foreach ($reservation['Instances'] ?: [] as $instance) {
          // Keep track of images that do not exist anymore
          // delete them after saving the rest of the images.
          if (isset($stale[$instance['InstanceId']])) {
            unset($stale[$instance['InstanceId']]);
          }

          // Store the Reservation OwnerId in instance so batch
          // callback has access.
          $instance['reservation_ownerid'] = $reservation['OwnerId'];
          $instance['reservation_id'] = $reservation['ReservationId'];
          Ec2BatchOperations::updateInstance($this->cloudContext, $instance);
        }
      }

      if (count($stale) && $clear === TRUE) {
        $this->entityTypeManager->getStorage($entity_type)->delete($stale);
      }

      $updated = TRUE;
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Update all Instances of all cloud region.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateAllInstances(array $params = [], $clear = FALSE) {
    $entity_type = 'aws_cloud_instance';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $cloud_configs = $this->entityTypeManager
      ->getStorage('cloud_config')
      ->loadByProperties([
        'type' => 'aws_cloud',
      ]);

    foreach ($cloud_configs ?: [] as $cloud_config) {
      $cloud_context = $cloud_config->getCloudContext();
      $this->setCloudContext($cloud_context);

      $updated = $this->updateInstanceEntities($params, $clear, $entity_type, $cloud_context);
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Call the API for updated entities and store them as Image entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   * @param string $entity_type
   *   Entity type string.
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateImageEntities(array $params = [], $clear = FALSE, $entity_type = '', $cloud_context = '') {
    $updated = FALSE;
    // Load all entities by cloud_context.
    $image_entities = $this->entityTypeManager
      ->getStorage($entity_type)
      ->loadByProperties([
        'cloud_context' => $this->cloudContext,
      ]);
    $result = $this->describeImages($params);

    if (isset($result)) {

      $this->cloudConfigPluginManager->setCloudContext($cloud_context);
      $cloud_config = $this->cloudConfigPluginManager->loadConfigEntity();

      $stale = [];
      // Make it easier to lookup the images by setting up
      // the array with the image_id.
      foreach ($image_entities ?: [] as $image) {
        $stale[$image->getImageId()] = $image;
      }

      /* @var \Drupal\Core\Batch\BatchBuilder $batch_builder */
      $batch_builder = $this->initBatch('Image Update');

      foreach ($result['Images'] ?: [] as $image) {
        // Keep track of images that do not exist anymore
        // delete them after saving the rest of the images.
        if (isset($stale[$image['ImageId']])) {
          unset($stale[$image['ImageId']]);
        }

        $batch_builder->addOperation([
          Ec2BatchOperations::class,
          'updateImage',
        ], [$cloud_context, $image]);
      }

      if (!array_key_exists('ImageIds', $params)) {
        // If  OwnerId of Image is not same as Account ID of Cloud Config,
        // it might be the public image.
        // Then try to call 'describeImages' with ImageId.
        foreach ($stale ?: [] as $entity) {
          if ($cloud_config->get('field_account_id')->value === $entity->getAccountId()) {
            continue;
          }
          $images = $this->describeImages([
            'ImageIds' => [$entity->getImageId()],
          ]);
          if (isset($images) && !empty($images['Images'])) {
            $image = reset($images['Images']);
            $batch_builder->addOperation([
              Ec2BatchOperations::class,
              'updateImage',
            ], [$this->cloudContext, $image]);

            unset($stale[$image['ImageId']]);
            $result['Images'][] = $image;
          }
        }
      }

      $batch_builder->addOperation([
        Ec2BatchOperations::class,
        'finished',
      ], [$entity_type, $stale, $clear]);
      $this->runBatch($batch_builder);
      $updated = count($result['Images']);
    }

    return $updated;
  }

  /**
   * Update the EC2Images.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateImages(array $params = [], $clear = FALSE) {
    $entity_type = 'aws_cloud_image';
    $lock_name = $this->getLockKey($entity_type);
    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $updated = $this->updateImageEntities($params, $clear, $entity_type, $this->cloudContext);

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * {@inheritdoc}
   */
  public function updateImagesWithoutBatch(array $params = [], $clear = FALSE) {
    $updated = FALSE;
    $entity_type = 'aws_cloud_image';
    $lock_name = $this->getLockKey($entity_type);
    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    // Load all entities by cloud_context.
    $image_entities = $this->entityTypeManager
      ->getStorage($entity_type)
      ->loadByProperties([
        'cloud_context' => $this->cloudContext,
      ]);
    $result = $this->describeImages($params);

    if (isset($result)) {

      $stale = [];
      // Make it easier to lookup the images by setting up
      // the array with the image_id.
      foreach ($image_entities ?: [] as $image) {
        $stale[$image->getImageId()] = $image;
      }

      foreach ($result['Images'] ?: [] as $image) {
        // Keep track of images that do not exist anymore
        // delete them after saving the rest of the images.
        if (isset($stale[$image['ImageId']])) {
          unset($stale[$image['ImageId']]);
        }

        Ec2BatchOperations::updateImage($this->cloudContext, $image);
      }

      if (count($stale) && $clear === TRUE) {
        $this->entityTypeManager->getStorage($entity_type)->delete($stale);
      }

      $updated = count($result['Images'] ?: []);
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Update all Images of all cloud region.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateAllImages(array $params = [], $clear = FALSE) {
    $entity_type = 'aws_cloud_image';
    $lock_name = $this->getLockKey($entity_type);
    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $cloud_configs = $this->entityTypeManager
      ->getStorage('cloud_config')
      ->loadByProperties([
        'type' => 'aws_cloud',
      ]);

    foreach ($cloud_configs ?: [] as $cloud_config) {
      $cloud_context = $cloud_config->getCloudContext();
      $this->setCloudContext($cloud_context);

      $updated = $this->updateImageEntities($params, $clear, $entity_type, $cloud_context);
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Call the API for updated entities and store them as SecurityGroup entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   * @param string $entity_type
   *   Entity type string.
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateSecurityGroupEntities(array $params = [], $clear = TRUE, $entity_type = '', $cloud_context = '') {
    $updated = FALSE;
    $result = $this->describeSecurityGroups($params);

    if (isset($result)) {

      $all_groups = $this->loadAllEntities($entity_type);
      $stale = [];
      // Make it easier to lookup the groups by setting up
      // the array with the group_id.
      foreach ($all_groups ?: [] as $group) {
        $stale[$group->getGroupId()] = $group;
      }

      /* @var \Drupal\Core\Batch\BatchBuilder $batch_builder */
      $batch_builder = $this->initBatch('Security Group Update');

      foreach ($result['SecurityGroups'] ?: [] as $security_group) {

        // Keep track of instances that do not exist anymore
        // delete them after saving the rest of the instances.
        if (isset($stale[$security_group['GroupId']])) {
          unset($stale[$security_group['GroupId']]);
        }
        $batch_builder->addOperation([
          Ec2BatchOperations::class,
          'updateSecurityGroup',
        ], [$cloud_context, $security_group]);
      }

      $batch_builder->addOperation([
        Ec2BatchOperations::class,
        'finished',
      ], [$entity_type, $stale, $clear]);
      $this->runBatch($batch_builder);
      $updated = TRUE;
    }

    return $updated;
  }

  /**
   * Update the EC2SecurityGroups.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateSecurityGroups(array $params = [], $clear = TRUE) {
    $entity_type = 'aws_cloud_security_group';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $updated = $this->updateSecurityGroupEntities($params, $clear, $entity_type, $this->cloudContext);

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Update all SecurityGroups of all cloud region.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateAllSecurityGroups(array $params = [], $clear = TRUE) {
    $entity_type = 'aws_cloud_security_group';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $cloud_configs = $this->entityTypeManager
      ->getStorage('cloud_config')
      ->loadByProperties([
        'type' => 'aws_cloud',
      ]);

    foreach ($cloud_configs ?: [] as $cloud_config) {
      $cloud_context = $cloud_config->getCloudContext();
      $this->setCloudContext($cloud_context);

      $updated = $this->updateSecurityGroupEntities($params, $clear, $entity_type, $cloud_context);
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * {@inheritdoc}
   */
  public function updateCloudServerTemplates(array $params = [], $clear = TRUE) {
    $updated = FALSE;
    $entity_type = 'cloud_server_template';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    // Load all entities by cloud_context.
    $entities = $this->entityTypeManager
      ->getStorage($entity_type)
      ->loadByProperties([
        'type' => 'aws_cloud',
        'cloud_context' => $this->cloudContext,
      ]);

    $result = $this->describeLaunchTemplates($params);

    if (isset($result)) {

      $stale = [];
      foreach ($entities ?: [] as $entity) {
        $stale[$entity->getName()] = $entity;
      }

      /* @var \Drupal\Core\Batch\BatchBuilder $batch_builder */
      $batch_builder = $this->initBatch('Launch Template Update');

      foreach ($result['LaunchTemplates'] ?: [] as $template) {
        if (isset($stale[$template['LaunchTemplateName']])) {
          unset($stale[$template['LaunchTemplateName']]);
        }
        $batch_builder->addOperation([
          Ec2BatchOperations::class,
          'updateCloudServerTemplate',
        ], [$this->cloudContext, $template]);
      }

      $batch_builder->addOperation([
        Ec2BatchOperations::class,
        'finished',
      ], [$entity_type, $stale, $clear]);
      $this->runBatch($batch_builder);
      $updated = TRUE;
    }
    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * {@inheritdoc}
   */
  public function updateCloudServerTemplatesWithoutBatch(array $params = [], $clear = TRUE) {
    $updated = FALSE;
    $entity_type = 'cloud_server_template';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    // Load all entities by cloud_context.
    $entities = $this->entityTypeManager
      ->getStorage($entity_type)
      ->loadByProperties([
        'type' => 'aws_cloud',
        'cloud_context' => $this->cloudContext,
      ]);

    $result = $this->describeLaunchTemplates($params);

    if (isset($result)) {

      $stale = [];
      foreach ($entities ?: [] as $entity) {
        $stale[$entity->getName()] = $entity;
      }

      foreach ($result['LaunchTemplates'] ?: [] as $template) {
        if (isset($stale[$template['LaunchTemplateName']])) {
          unset($stale[$template['LaunchTemplateName']]);
        }

        Ec2BatchOperations::updateCloudServerTemplate($this->cloudContext, $template);
      }

      if (count($stale) && $clear === TRUE) {
        $this->entityTypeManager->getStorage($entity_type)->delete($stale);
      }

      $updated = TRUE;
    }
    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * {@inheritdoc}
   */
  public function setupIpPermissions(&$security_group, $field, array $ec2_permissions) {
    // Permissions are always overwritten with the latest from
    // EC2.  The reason is that there is no way to guarantee a 1
    // to 1 mapping from the $security_group['IpPermissions'] array.
    // There is no IP permission ID coming back from EC2.
    // Clear out all items before re-adding them.
    $i = $security_group->$field->count() - 1;
    while ($i >= 0) {
      if ($security_group->$field->get($i)) {
        $security_group->$field->removeItem($i);
      }
      $i--;
    }

    // Setup all permission objects.
    $count = 0;
    foreach ($ec2_permissions ?: [] as $permissions) {
      $permission_objects = $this->setupIpPermissionObject($permissions);
      // Loop through the permission objects and add them to the
      // security group ip_permission field.
      foreach ($permission_objects ?: [] as $permission) {
        $security_group->$field->set($count, $permission);
        $count++;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setupIpPermissionObject(array $ec2_permission) {
    $ip_permissions = [];

    // Get the field definition for an IpPermission object.
    $definition = $this->entityFieldManager->getBaseFieldDefinitions('aws_cloud_security_group');

    // Setup the more global attributes.
    $from_port = isset($ec2_permission['FromPort']) ? (string) ($ec2_permission['FromPort']) : NULL;
    $to_port = isset($ec2_permission['ToPort']) ? (string) ($ec2_permission['ToPort']) : NULL;
    $ip_protocol = $ec2_permission['IpProtocol'];

    // To keep things consistent, if ip_protocol is -1,
    // set from_port and to_port as 0-65535.
    if ($ip_protocol === '-1') {
      $from_port = '0';
      $to_port = '65535';
    }

    if (!empty($ec2_permission['IpRanges'])) {
      // Create a IPv4 permission object.
      foreach ($ec2_permission['IpRanges'] ?: [] as $ip_range) {
        $ip_range_permission = $this->fieldTypePluginManager->createInstance('ip_permission', [
          'field_definition' => $definition['ip_permission'],
          'parent' => NULL,
          'name' => NULL,
        ]);
        // Source is an internal identifier.  Doesn't come from EC2.
        $ip_range_permission->source = 'ip4';
        $ip_range_permission->cidr_ip = $ip_range['CidrIp'];

        $ip_range_permission->from_port = $from_port;
        $ip_range_permission->to_port = $to_port;
        $ip_range_permission->ip_protocol = $ip_protocol;

        $ip_permissions[] = $ip_range_permission;
      }
    }

    if (!empty($ec2_permission['Ipv6Ranges'])) {
      // Create IPv6 permissions object.
      foreach ($ec2_permission['Ipv6Ranges'] ?: [] as $ip_range) {
        $ip_v6_permission = $this->fieldTypePluginManager->createInstance('ip_permission', [
          'field_definition' => $definition['ip_permission'],
          'parent' => NULL,
          'name' => NULL,
        ]);
        // Source is an internal identifier.  Doesn't come from EC2.
        $ip_v6_permission->source = 'ip6';
        $ip_v6_permission->cidr_ip_v6 = $ip_range['CidrIpv6'];
        $ip_v6_permission->from_port = $from_port;
        $ip_v6_permission->to_port = $to_port;
        $ip_v6_permission->ip_protocol = $ip_protocol;
        $ip_permissions[] = $ip_v6_permission;
      }
    }

    if (!empty($ec2_permission['UserIdGroupPairs'])) {
      // Create Group permissions object.
      foreach ($ec2_permission['UserIdGroupPairs'] ?: [] as $group) {
        $group_permission = $this->fieldTypePluginManager->createInstance('ip_permission', [
          'field_definition' => $definition['ip_permission'],
          'parent' => NULL,
          'name' => NULL,
        ]);
        // Source is an internal identifier.  Doesn't come from EC2.
        $group_permission->source = 'group';
        $group_permission->group_id = $group['GroupId'] ?? NULL;
        $group_permission->group_name = $group['GroupName'] ?? NULL;
        $group_permission->user_id = $group['UserId'] ?? NULL;
        $group_permission->peering_status = $group['PeeringStatus'] ?? NULL;
        $group_permission->vpc_id = $group['VpcId'] ?? NULL;
        $group_permission->peering_connection_id = $group['VpcPeeringConnectionId'] ?? NULL;
        $group_permission->from_port = $from_port;
        $group_permission->to_port = $to_port;
        $group_permission->ip_protocol = $ip_protocol;
        $ip_permissions[] = $group_permission;
      }
    }

    if (!empty($ec2_permission['PrefixListIds'])) {
      foreach ($ec2_permission['PrefixListIds'] ?: [] as $prefix) {
        $prefix_permission = $this->fieldTypePluginManager->createInstance('ip_permission', [
          'field_definition' => $definition['ip_permission'],
          'parent' => NULL,
          'name' => NULL,
        ]);
        // Source is an internal identifier.  Doesn't come from EC2.
        $prefix_permission->source = 'prefix';
        $prefix_permission->prefix_list_id = $prefix['PrefixListId'];
        $prefix_permission->from_port = $from_port;
        $prefix_permission->to_port = $to_port;
        $prefix_permission->ip_protocol = $ip_protocol;
        $ip_permissions[] = $prefix_permission;
      }
    }
    return $ip_permissions;
  }

  /**
   * Call API for updated entities and store them as NetworkInterface entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   * @param string $entity_type
   *   Entity type string.
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateNetworkInterfaceEntities(array $params = [], $clear = TRUE, $entity_type = '', $cloud_context = '') {
    $updated = FALSE;
    $result = $this->describeNetworkInterfaces($params);

    if (isset($result)) {

      $all_interfaces = $this->loadAllEntities($entity_type);
      $stale = [];
      // Make it easier to lookup the groups by setting up
      // the array with the group_id.
      foreach ($all_interfaces ?: [] as $interface) {
        $stale[$interface->getNetworkInterfaceId()] = $interface;
      }

      /* @var \Drupal\Core\Batch\BatchBuilder $batch_builder */
      $batch_builder = $this->initBatch('Network Interface Update');

      foreach ($result['NetworkInterfaces'] ?: [] as $network_interface) {
        // Keep track of network interfaces that do not exist anymore
        // delete them after saving the rest of the network interfaces.
        if (isset($stale[$network_interface['NetworkInterfaceId']])) {
          unset($stale[$network_interface['NetworkInterfaceId']]);
        }
        $batch_builder->addOperation([
          Ec2BatchOperations::class,
          'updateNetworkInterface',
        ], [$cloud_context, $network_interface]);
      }

      $batch_builder->addOperation([
        Ec2BatchOperations::class,
        'finished',
      ], [$entity_type, $stale, $clear]);
      $this->runBatch($batch_builder);
      $updated = TRUE;
    }
    return $updated;
  }

  /**
   * Update the EC2NetworkInterfaces.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateNetworkInterfaces(array $params = [], $clear = TRUE) {
    $entity_type = 'aws_cloud_network_interface';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $updated = $this->updateNetworkInterfaceEntities($params, $clear, $entity_type, $this->cloudContext);

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Update all NetworkInterfaces of all cloud region.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateAllNetworkInterfaces(array $params = [], $clear = TRUE) {
    $entity_type = 'aws_cloud_network_interface';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $cloud_configs = $this->entityTypeManager
      ->getStorage('cloud_config')
      ->loadByProperties([
        'type' => 'aws_cloud',
      ]);

    foreach ($cloud_configs ?: [] as $cloud_config) {
      $cloud_context = $cloud_config->getCloudContext();
      $this->setCloudContext($cloud_context);

      $updated = $this->updateNetworkInterfaceEntities($params, $clear, $entity_type, $cloud_context);
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Call the API for updated entities and store them as Elastic Ip entities.
   *
   * @param string $entity_type
   *   Entity type string.
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateElasticIpEntities($entity_type = '', $cloud_context = '') {
    $updated = FALSE;
    $result = $this->describeAddresses();

    if (isset($result)) {

      $all_ips = $this->loadAllEntities($entity_type);
      $stale = [];
      // Make it easier to lookup the groups by setting up
      // the array with the group_id.
      foreach ($all_ips ?: [] as $ip) {
        $stale[$ip->getPublicIp()] = $ip;
      }

      /* @var \Drupal\Core\Batch\BatchBuilder $batch_builder */
      $batch_builder = $this->initBatch('ElasticIp Update');

      foreach ($result['Addresses'] ?: [] as $elastic_ip) {
        // Keep track of IPs that do not exist anymore
        // delete them after saving the rest of the IPs.
        if (isset($stale[$elastic_ip['PublicIp']])) {
          unset($stale[$elastic_ip['PublicIp']]);
        }
        $batch_builder->addOperation([
          Ec2BatchOperations::class,
          'updateElasticIp',
        ], [$cloud_context, $elastic_ip]);
      }

      $batch_builder->addOperation([
        Ec2BatchOperations::class,
        'finished',
      ], [$entity_type, $stale, TRUE]);
      $this->runBatch($batch_builder);
      $updated = TRUE;
    }

    return $updated;
  }

  /**
   * Update the EC2ElasticIps.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateElasticIp() {
    $entity_type = 'aws_cloud_elastic_ip';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $updated = $this->updateElasticIpEntities($entity_type, $this->cloudContext);

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Update all Elastic Ips of all cloud region.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateAllElasticIp() {
    $entity_type = 'aws_cloud_elastic_ip';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $cloud_configs = $this->entityTypeManager
      ->getStorage('cloud_config')
      ->loadByProperties([
        'type' => 'aws_cloud',
      ]);

    foreach ($cloud_configs ?: [] as $cloud_config) {
      $cloud_context = $cloud_config->getCloudContext();
      $this->setCloudContext($cloud_context);

      $updated = $this->updateElasticIpEntities($entity_type, $cloud_context);
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Call the API for updated entities and store them as KeyPair entities.
   *
   * @param string $entity_type
   *   Entity type string.
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateKeyPairEntities($entity_type = '', $cloud_context = '') {
    $updated = FALSE;
    $result = $this->describeKeyPairs();

    if (isset($result)) {

      $all_keys = $this->loadAllEntities($entity_type);
      $stale = [];
      // Make it easier to lookup the groups by setting up
      // the array with the group_id.
      foreach ($all_keys ?: [] as $key) {
        $stale[$key->getKeyPairName()] = $key;
      }
      /* @var \Drupal\Core\Batch\BatchBuilder $batch_builder */
      $batch_builder = $this->initBatch('Keypair Update');

      foreach ($result['KeyPairs'] ?: [] as $key_pair) {
        // Keep track of key pair that do not exist anymore
        // delete them after saving the rest of the key pair.
        if (isset($stale[$key_pair['KeyName']])) {
          unset($stale[$key_pair['KeyName']]);
        }
        $batch_builder->addOperation([
          Ec2BatchOperations::class,
          'updateKeyPair',
        ], [$cloud_context, $key_pair]);
      }

      $batch_builder->addOperation([
        Ec2BatchOperations::class,
        'finished',
      ], [$entity_type, $stale, TRUE]);
      $this->runBatch($batch_builder);
      $updated = TRUE;
    }

    return $updated;
  }

  /**
   * Update the EC2KeyPairs.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateKeyPairs() {
    $entity_type = 'aws_cloud_key_pair';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $updated = $this->updateKeyPairEntities($entity_type, $this->cloudContext);

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Update all KeyPairs of all cloud region.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateAllKeyPairs() {
    $entity_type = 'aws_cloud_key_pair';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $cloud_configs = $this->entityTypeManager
      ->getStorage('cloud_config')
      ->loadByProperties([
        'type' => 'aws_cloud',
      ]);

    foreach ($cloud_configs ?: [] as $cloud_config) {
      $cloud_context = $cloud_config->getCloudContext();
      $this->setCloudContext($cloud_context);

      $updated = $this->updateKeyPairEntities($entity_type, $cloud_context);
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Call the API for updated entities and store them as Volume entities.
   *
   * @param string $entity_type
   *   Entity type string.
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateVolumeEntities($entity_type = '', $cloud_context = '') {
    $updated = FALSE;
    $result = $this->describeVolumes();

    if (isset($result)) {

      $all_volumes = $this->loadAllEntities($entity_type);
      $stale = [];
      // Make it easier to lookup the groups by setting up
      // the array with the group_id.
      foreach ($all_volumes ?: [] as $volume) {
        $stale[$volume->getVolumeId()] = $volume;
      }
      $snapshot_id_name_map = $this->getSnapshotIdNameMap($result['Volumes'] ?: []);

      /* @var \Drupal\Core\Batch\BatchBuilder $batch_builder */
      $batch_builder = $this->initBatch('Volume Update');

      foreach ($result['Volumes'] ?: [] as $volume) {
        // Keep track of network interfaces that do not exist anymore
        // delete them after saving the rest of the network interfaces.
        if (isset($stale[$volume['VolumeId']])) {
          unset($stale[$volume['VolumeId']]);
        }
        $batch_builder->addOperation([
          Ec2BatchOperations::class,
          'updateVolume',
        ], [$cloud_context, $volume, $snapshot_id_name_map]);
      }

      $batch_builder->addOperation([
        Ec2BatchOperations::class,
        'finished',
      ], [$entity_type, $stale, TRUE]);
      $this->runBatch($batch_builder);
      $updated = TRUE;
    }

    return $updated;
  }

  /**
   * Update the EC2Volumes.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateVolumes() {
    $entity_type = 'aws_cloud_volume';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $updated = $this->updateVolumeEntities($entity_type, $this->cloudContext);

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Update all Volumes of all cloud region.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateAllVolumes() {
    $entity_type = 'aws_cloud_volume';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $cloud_configs = $this->entityTypeManager
      ->getStorage('cloud_config')
      ->loadByProperties([
        'type' => 'aws_cloud',
      ]);

    foreach ($cloud_configs ?: [] as $cloud_config) {
      $cloud_context = $cloud_config->getCloudContext();
      $this->setCloudContext($cloud_context);

      $updated = $this->updateVolumeEntities($entity_type, $cloud_context);
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Call the API for updated entities and store them as Snapshot entities.
   *
   * @param string $entity_type
   *   Entity type string.
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateSnapshotEntities($entity_type = '', $cloud_context = '') {
    $updated = FALSE;
    $result = $this->describeSnapshots();

    if (isset($result)) {

      $all_snapshots = $this->loadAllEntities($entity_type);
      $stale = [];
      // Make it easier to lookup the snapshot by setting up
      // the array with the snapshot_id.
      foreach ($all_snapshots ?: [] as $snapshot) {
        $stale[$snapshot->getSnapshotId()] = $snapshot;
      }
      /* @var \Drupal\Core\Batch\BatchBuilder $batch_builder */
      $batch_builder = $this->initBatch('Snapshot Update');

      foreach ($result['Snapshots'] ?: [] as $snapshot) {
        // Keep track of snapshot that do not exist anymore
        // delete them after saving the rest of the snapshots.
        if (isset($stale[$snapshot['SnapshotId']])) {
          unset($stale[$snapshot['SnapshotId']]);
        }

        $batch_builder->addOperation([
          Ec2BatchOperations::class,
          'updateSnapshot',
        ], [$cloud_context, $snapshot]);
      }

      $batch_builder->addOperation([
        Ec2BatchOperations::class,
        'finished',
      ], [$entity_type, $stale, TRUE]);
      $this->runBatch($batch_builder);
      $updated = TRUE;
    }

    return $updated;
  }

  /**
   * Update the EC2Snapshots.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateSnapshots() {
    $entity_type = 'aws_cloud_snapshot';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $updated = $this->updateSnapshotEntities($entity_type, $this->cloudContext);

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Update all Snapshots of all cloud region.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateAllSnapshots() {
    $entity_type = 'aws_cloud_snapshot';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $cloud_configs = $this->entityTypeManager
      ->getStorage('cloud_config')
      ->loadByProperties([
        'type' => 'aws_cloud',
      ]);

    foreach ($cloud_configs ?: [] as $cloud_config) {
      $cloud_context = $cloud_config->getCloudContext();
      $this->setCloudContext($cloud_context);

      $updated = $this->updateSnapshotEntities($entity_type, $cloud_context);
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Call the API for updated entities and store them as Vpc entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   * @param string $entity_type
   *   Entity type string.
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateVpcEntities(array $params = [], $clear = TRUE, $entity_type = '', $cloud_context = '') {
    $updated = FALSE;
    $result = $this->describeVpcs($params);

    if (isset($result)) {

      $all_vpcs = $this->loadAllEntities($entity_type);
      $stale = [];
      // Make it easier to lookup the snapshot by setting up
      // the array with the vpc_id.
      foreach ($all_vpcs ?: [] as $vpc) {
        $stale[$vpc->getVpcId()] = $vpc;
      }
      /* @var \Drupal\Core\Batch\BatchBuilder $batch_builder */
      $batch_builder = $this->initBatch('VPC Update');

      foreach ($result['Vpcs'] ?: [] as $vpc) {
        // Keep track of snapshot that do not exist anymore
        // delete them after saving the rest of the snapshots.
        if (isset($stale[$vpc['VpcId']])) {
          unset($stale[$vpc['VpcId']]);
        }

        $batch_builder->addOperation([
          Ec2BatchOperations::class,
          'updateVpc',
        ], [$this->cloudContext, $vpc]);
      }

      $batch_builder->addOperation([
        Ec2BatchOperations::class,
        'finished',
      ], [$entity_type, $stale, $clear]);
      $this->runBatch($batch_builder);
      $updated = TRUE;
    }

    return $updated;
  }

  /**
   * Update the EC2Vpcs.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateVpcs(array $params = [], $clear = TRUE) {
    $entity_type = 'aws_cloud_vpc';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $updated = $this->updateVpcEntities($params, $clear, $entity_type, $this->cloudContext);

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * {@inheritdoc}
   */
  public function updateVpcsWithoutBatch(array $params = [], $clear = TRUE) {
    $updated = FALSE;
    $entity_type = 'aws_cloud_vpc';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $result = $this->describeVpcs($params);

    if (isset($result)) {

      $all_vpcs = $this->loadAllEntities($entity_type);
      $stale = [];
      // Make it easier to lookup the snapshot by setting up
      // the array with the vpc_id.
      foreach ($all_vpcs ?: [] as $vpc) {
        $stale[$vpc->getVpcId()] = $vpc;
      }

      foreach ($result['Vpcs'] ?: [] as $vpc) {
        // Keep track of snapshot that do not exist anymore
        // delete them after saving the rest of the snapshots.
        if (isset($stale[$vpc['VpcId']])) {
          unset($stale[$vpc['VpcId']]);
        }

        Ec2BatchOperations::updateVpc($this->cloudContext, $vpc);
      }

      if (count($stale) && $clear === TRUE) {
        $this->entityTypeManager->getStorage($entity_type)->delete($stale);
      }

      $updated = TRUE;
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Update all Vpcs of all cloud region.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateAllVpcs(array $params = [], $clear = TRUE) {
    $entity_type = 'aws_cloud_vpc';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $cloud_configs = $this->entityTypeManager
      ->getStorage('cloud_config')
      ->loadByProperties([
        'type' => 'aws_cloud',
      ]);

    foreach ($cloud_configs ?: [] as $cloud_config) {
      $cloud_context = $cloud_config->getCloudContext();
      $this->setCloudContext($cloud_context);

      $updated = $this->updateVpcEntities($params, $clear, $entity_type, $cloud_context);
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Call the API for updated entities.
   *
   * Store them as VpcPeeringConnection entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   * @param string $entity_type
   *   Entity type string.
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateVpcPeeringConnectionEntities(array $params = [], $clear = TRUE, $entity_type = '', $cloud_context = '') {
    $updated = FALSE;
    $result = $this->describeVpcPeeringConnections($params);
    if (isset($result)) {

      $all_vpc_peering_connections = $this->loadAllEntities($entity_type);
      $stale = [];
      // Make it easier to lookup the snapshot by setting up
      // the array with the vpc_id.
      foreach ($all_vpc_peering_connections as $vpc_peering_connection) {
        $stale[$vpc_peering_connection->getVpcPeeringConnectionId()] = $vpc_peering_connection;
      }
      /* @var \Drupal\Core\Batch\BatchBuilder $batch_builder */
      $batch_builder = $this->initBatch('VPC Peering Connection Update');

      foreach ($result['VpcPeeringConnections'] ?: [] as $vpc_peering_connection) {
        // Keep track of snapshot that do not exist anymore
        // delete them after saving the rest of the snapshots.
        if (isset($stale[$vpc_peering_connection['VpcPeeringConnectionId']])) {
          unset($stale[$vpc_peering_connection['VpcPeeringConnectionId']]);
        }

        $batch_builder->addOperation([
          Ec2BatchOperations::class,
          'updateVpcPeeringConnection',
        ], [$cloud_context, $vpc_peering_connection]);
      }

      $batch_builder->addOperation([
        Ec2BatchOperations::class,
        'finished',
      ], [$entity_type, $stale, $clear]);
      $this->runBatch($batch_builder);
      $updated = TRUE;
    }

    return $updated;
  }

  /**
   * Update the EC2VpcPeeringConnections.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateVpcPeeringConnections(array $params = [], $clear = TRUE) {
    $entity_type = 'aws_cloud_vpc_peering_connection';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $updated = $this->updateVpcPeeringConnectionEntities($params, $clear, $entity_type, $this->cloudContext);

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Update all VpcPeeringConnections of all cloud region.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateAllVpcPeeringConnections(array $params = [], $clear = TRUE) {
    $entity_type = 'aws_cloud_vpc_peering_connection';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $cloud_configs = $this->entityTypeManager
      ->getStorage('cloud_config')
      ->loadByProperties([
        'type' => 'aws_cloud',
      ]);

    foreach ($cloud_configs ?: [] as $cloud_config) {
      $cloud_context = $cloud_config->getCloudContext();
      $this->setCloudContext($cloud_context);

      $updated = $this->updateVpcPeeringConnectionEntities($params, $clear, $entity_type, $cloud_context);
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * {@inheritdoc}
   */
  public function updateVpcPeeringConnectionsWithoutBatch(array $params = [], $clear = TRUE) {
    $updated = FALSE;
    $entity_type = 'aws_cloud_vpc_peering_connection';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $result = $this->describeVpcPeeringConnections($params);

    if (isset($result)) {

      $all_vpc_peering_connections = $this->loadAllEntities($entity_type);
      $stale = [];
      // Make it easier to lookup the snapshot by setting up
      // the array with the vpc_id.
      foreach ($all_vpc_peering_connections as $vpc_peering_connection) {
        $stale[$vpc_peering_connection->getVpcPeeringConnectionId()] = $vpc_peering_connection;
      }

      foreach ($result['VpcPeeringConnections'] ?: [] as $vpc_peering_connection) {
        // Keep track of snapshot that do not exist anymore
        // delete them after saving the rest of the snapshots.
        if (isset($stale[$vpc_peering_connection['VpcPeeringConnectionId']])) {
          unset($stale[$vpc_peering_connection['VpcPeeringConnectionId']]);
        }

        Ec2BatchOperations::updateVpcPeeringConnection($this->cloudContext, $vpc_peering_connection);
      }

      if (count($stale) && $clear === TRUE) {
        $this->entityTypeManager->getStorage($entity_type)->delete($stale);
      }
      $updated = TRUE;
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Call the API for updated entities and store them as Subnet entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   * @param string $entity_type
   *   Entity type string.
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateSubnetEntities(array $params = [], $clear = TRUE, $entity_type = '', $cloud_context = '') {
    $updated = FALSE;
    $result = $this->describeSubnets($params);

    if (isset($result)) {

      $all_subnets = $this->loadAllEntities($entity_type);
      $stale = [];
      // Make it easier to lookup the subnet by setting up
      // the array with the subnet_id.
      foreach ($all_subnets ?: [] as $subnet) {
        $stale[$subnet->getSubnetId()] = $subnet;
      }
      /* @var \Drupal\Core\Batch\BatchBuilder $batch_builder */
      $batch_builder = $this->initBatch('Subnet Update');

      foreach ($result['Subnets'] ?: [] as $subnet) {

        // Keep track of subnet that do not exist anymore
        // delete them after saving the rest of the snapshots.
        if (isset($stale[$subnet['SubnetId']])) {
          unset($stale[$subnet['SubnetId']]);
        }

        $batch_builder->addOperation([
          Ec2BatchOperations::class,
          'updateSubnet',
        ], [$cloud_context, $subnet]);
      }

      $batch_builder->addOperation([
        Ec2BatchOperations::class,
        'finished',
      ], [$entity_type, $stale, $clear]);
      $this->runBatch($batch_builder);
      $updated = TRUE;
    }

    return $updated;
  }

  /**
   * Update the EC2Subnets.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateSubnets(array $params = [], $clear = TRUE) {
    $entity_type = 'aws_cloud_subnet';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $updated = $this->updateSubnetEntities($params, $clear, $entity_type, $this->cloudContext);

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * {@inheritdoc}
   */
  public function updateSubnetsWithoutBatch(array $params = [], $clear = TRUE) {
    $updated = FALSE;
    $entity_type = 'aws_cloud_subnet';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $result = $this->describeSubnets($params);

    if (isset($result)) {

      $all_subnets = $this->loadAllEntities($entity_type);
      $stale = [];
      // Make it easier to lookup the subnet by setting up
      // the array with the subnet_id.
      foreach ($all_subnets ?: [] as $subnet) {
        $stale[$subnet->getSubnetId()] = $subnet;
      }

      foreach ($result['Subnets'] ?: [] as $subnet) {

        // Keep track of subnet that do not exist anymore
        // delete them after saving the rest of the snapshots.
        if (isset($stale[$subnet['SubnetId']])) {
          unset($stale[$subnet['SubnetId']]);
        }

        Ec2BatchOperations::updateSubnet($this->cloudContext, $subnet);
      }

      if (count($stale) && $clear === TRUE) {
        $this->entityTypeManager->getStorage($entity_type)->delete($stale);
      }

      $updated = TRUE;
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Update all Subnets of all cloud region.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateAllSubnets(array $params = [], $clear = TRUE) {
    $entity_type = 'aws_cloud_subnet';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $cloud_configs = $this->entityTypeManager
      ->getStorage('cloud_config')
      ->loadByProperties([
        'type' => 'aws_cloud',
      ]);

    foreach ($cloud_configs ?: [] as $cloud_config) {
      $cloud_context = $cloud_config->getCloudContext();
      $this->setCloudContext($cloud_context);

      $updated = $this->updateSubnetEntities($params, $clear, $entity_type, $cloud_context);
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * {@inheritdoc}
   */
  public function getVpcs() {
    $vpcs = [];
    $result = $this->describeVpcs();
    if (isset($result)) {
      foreach (array_column($result['Vpcs'] ?: [], 'VpcId') as $key => $vpc) {
        $vpcs[$vpc] = $result['Vpcs'][$key]['CidrBlock'] . " ($vpc)";
      }
    }
    return $vpcs;
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailabilityZones() {
    $zones = [];
    $result = $this->describeAvailabilityZones();
    if (isset($result)) {
      foreach (array_column($result['AvailabilityZones'] ?: [], 'ZoneName') as $availability_zone) {
        $zones[$availability_zone] = $availability_zone;
      }
    }
    return $zones;
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedPlatforms() {
    $platforms = [];
    $result = $this->describeAccountAttributes([
      'AttributeNames' => [
        'supported-platforms',
      ],
    ]);
    if (isset($result)) {
      foreach ($result['AccountAttributes'] ?: [] as $attribute) {
        if ($attribute['AttributeName'] === 'supported-platforms') {
          foreach ($attribute['AttributeValues'] ?: [] as $value) {
            $platforms[] = $value['AttributeValue'];
          }
        }
      }
    }
    return $platforms;
  }

  /**
   * {@inheritdoc}
   */
  public function getConsoleOutput(array $params = []) {
    $params += $this->getDefaultParameters();
    return $this->execute('GetConsoleOutput', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function clearAllEntities() {
    $timestamp = $this->getTimestamp();
    $this->clearEntities('aws_cloud_instance', $timestamp);
    $this->clearEntities('aws_cloud_security_group', $timestamp);
    $this->clearEntities('aws_cloud_image', $timestamp);
    $this->clearEntities('aws_cloud_network_interface', $timestamp);
    $this->clearEntities('aws_cloud_elastic_ip', $timestamp);
    $this->clearEntities('aws_cloud_key_pair', $timestamp);
    $this->clearEntities('aws_cloud_volume', $timestamp);
    $this->clearEntities('aws_cloud_snapshot', $timestamp);
    $this->clearEntities('aws_cloud_vpc', $timestamp);
    $this->clearEntities('aws_cloud_subnet', $timestamp);
  }

  /**
   * Helper method to get the current timestamp.
   *
   * @return int
   *   The current timestamp.
   */
  protected function getTimestamp() {
    return time();
  }

  /**
   * Setup the default parameters that all API calls will need.
   *
   * @return array
   *   Array of default parameters.
   */
  protected function getDefaultParameters() {
    return [
      'DryRun' => $this->dryRun,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSnapshotIdNameMap(array $volumes = []) {

    $snapshot_ids = array_filter(array_column($volumes, 'SnapshotId'));
    if (empty($snapshot_ids)) {
      return [];
    }

    $map = [];
    foreach ($snapshot_ids ?: [] as $snapshot_id) {
      $map[$snapshot_id] = '';
    }

    $result = $this->describeSnapshots();

    if (isset($result)) {

      foreach ($result['Snapshots'] ?: [] as $snapshot) {
        $snapshot_id = $snapshot['SnapshotId'];
        if (!array_key_exists($snapshot_id, $map)) {
          continue;
        }

        $map[$snapshot_id] = $this->getTagName($snapshot, '');
      }
    }

    return $map;
  }

  /**
   * {@inheritdoc}
   */
  public function getTagName(array $aws_obj, $default_value) {
    $name = $default_value;
    if (!isset($aws_obj['Tags'])) {
      return $name;
    }

    foreach ($aws_obj['Tags'] ?: [] as $tag) {
      if ($tag['Key'] === 'Name' && !empty($tag['Value'])) {
        $name = $tag['Value'];
        break;
      }
    }
    return $name;
  }

  /**
   * {@inheritdoc}
   */
  public function getPrivateIps(array $network_interfaces) {
    $ip_string = FALSE;
    $private_ips = [];
    foreach ($network_interfaces ?: [] as $interface) {
      $private_ips[] = $interface['PrivateIpAddress'];
    }
    if (count($private_ips)) {
      $ip_string = implode(', ', $private_ips);
    }
    return $ip_string;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateInstanceCost(array $instance, array $instance_types) {
    $cost = NULL;
    if ($instance['State']['Name'] === 'stopped') {
      return $cost;
    }

    $instance_type = $instance['InstanceType'];
    if (isset($instance_types[$instance_type])) {
      $parts = explode(':', $instance_types[$instance_type]);
      $hourly_rate = $parts[4];
      $launch_time = strtotime($instance['LaunchTime']->__toString());
      $cost = round((time() - $launch_time) / 3600 * $hourly_rate, 2);
    }
    return $cost;
  }

  /**
   * {@inheritdoc}
   */
  public function getUidTagValue(array $tags_array, $key) {
    $uid = 0;
    if (isset($tags_array['Tags'])) {
      foreach ($tags_array['Tags'] ?: [] as $tag) {
        if ($tag['Key'] === $key) {
          $uid = $tag['Value'];
          break;
        }
      }
    }
    return $uid;
  }

  /**
   * {@inheritdoc}
   */
  public function getInstanceUid($instance_id) {
    $uid = 0;
    $instance = $this->entityTypeManager
      ->getStorage('aws_cloud_instance')
      ->loadByProperties([
        'instance_id' => $instance_id,
      ]);

    if (count($instance) > 0) {
      $instance = array_shift($instance);
      $uid = $instance->getOwnerId();
    }
    return $uid;
  }

  /**
   * {@inheritdoc}
   */
  public function clearPluginCache() {
    $this->cloudConfigPluginManager->clearCachedDefinitions();
  }

  /**
   * Initialize a new batch builder.
   *
   * @param string $batch_name
   *   The batch name.
   *
   * @return \Drupal\Core\Batch\BatchBuilder
   *   The initialized batch object.
   */
  protected function initBatch($batch_name) {
    return (new BatchBuilder())
      ->setTitle($batch_name);
  }

  /**
   * Run the batch job to process entities.
   *
   * @param \Drupal\Core\Batch\BatchBuilder $batch_builder
   *   The batch builder object.
   */
  protected function runBatch(BatchBuilder $batch_builder) {
    // Log the start time.
    $start = $this->getTimestamp();
    $batch_array = $batch_builder->toArray();
    batch_set($batch_array);
    // Reset the progressive so batch works with out a web head.
    $batch = &batch_get();
    $batch['progressive'] = FALSE;
    batch_process();
    // Log the end time.
    $end = $this->getTimestamp();
    $this->logger('ec2_service')->info(
      $this->t('@updater - @cloud_context: Batch operation took @time seconds.',
        [
          '@cloud_context' => $this->cloudContext,
          '@updater' => $batch_array['title'],
          '@time' => $end - $start,
        ]));
  }

  /**
   * Generate a lock key based on entity name.
   *
   * @param string $name
   *   The entity name.
   *
   * @return string
   *   The lock key.
   */
  protected function getLockKey($name) {
    return $this->cloudContext . '_' . $name;
  }

  /**
   * Helper static method to clear cache.
   */
  public static function clearCacheValue() {
    \Drupal::cache('menu')->invalidateAll();
    \Drupal::service('cache.render')->deleteAll();
    \Drupal::service('router.builder')->rebuild();
    \Drupal::service('plugin.cache_clearer')->clearCachedDefinitions();
  }

  /**
   * Ping the metadata server for security credentials URL.
   *
   * @return bool
   *   TRUE if either 169.254.169.254 (EC2) or 169.254.170.2 (ECS) is
   *   accessible.
   */
  public function pingMetadataSecurityServer(): bool {

    $pinged = FALSE;

    // Instance Profile (IAM Instance Role).
    $metadata_urls[] = 'http://169.254.169.254/latest/meta-data/iam/security-credentials/';

    // IAM Roles for ECS Tasks.
    $metadata_urls[] = array_key_exists('AWS_CONTAINER_CREDENTIALS_RELATIVE_URI', $_ENV)
      ? "http://169.254.170.2{$_ENV['AWS_CONTAINER_CREDENTIALS_RELATIVE_URI']}"
      : NULL;

    foreach ($metadata_urls ?: [] as $metadata_url) {
      try {
        $client = \Drupal::httpClient();
        $client->get($metadata_url);
        $pinged = TRUE;
        break;
      }
      catch (\Exception $e) {
        $this->logger('aws_cloud')->notice($e->getMessage());
        $pinged = FALSE;
      }
    }

    return $pinged;
  }

  /**
   * {@inheritdoc}
   */
  public function terminateExpiredInstances() {
    // Terminate instances past the timestamp.
    $instances = aws_cloud_get_expired_instances($this->cloudContext);
    if (!empty($instances)) {
      $this->logger('aws_cloud')->notice(
        $this->t('Terminating the following instances %instance',
          ['%instance' => implode(', ', $instances['InstanceIds'])]
        )
      );
      $this->terminateInstance($instances);
    }
  }

  /**
   * Get image IDs of pending images.
   *
   * @return array
   *   Images IDs of pending images.
   */
  private function getPendingImageIds() {
    $image_ids = [];
    $entity_storage = $this->entityTypeManager->getStorage('aws_cloud_image');
    $entity_ids = $entity_storage
      ->getQuery()
      ->condition('status', 'pending')
      ->condition('cloud_context', $this->cloudContext)
      ->execute();
    $entities = $entity_storage->loadMultiple($entity_ids);
    foreach ($entities ?: [] as $entity) {
      $image_ids[] = $entity->getImageId();
    }
    return $image_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function updatePendingImages() {
    $image_ids = $this->getPendingImageIds($this->cloudContext);
    if (count($image_ids)) {
      $this->updateImages([
        'ImageIds' => $image_ids,
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createResourceQueueItems() {
    $update_resources_queue = $this->queueFactory->get('aws_cloud_update_resources_queue');
    $method_names = [
      'terminateExpiredInstances',
      'updateInstances',
      'updateSecurityGroups',
      'updateKeyPairs',
      'updateElasticIp',
      'updateNetworkInterfaces',
      'updateSnapshots',
      'updateVolumes',
      'updateVpcs',
      'updateVpcPeeringConnections',
      'updateSubnets',
      'updateCloudServerTemplates',
      'updatePendingImages',
      'updateAllInstances',
      'updateAllImages',
      'updateAllSecurityGroups',
      'updateAllNetworkInterfaces',
      'updateAllElasticIp',
      'updateAllKeyPairs',
      'updateAllVolumes',
      'updateAllSnapshots',
      'updateAllVpcs',
      'updateAllVpcPeeringConnections',
      'updateAllSubnets',
    ];
    foreach ($method_names as $method_name) {
      $update_resources_queue->createItem([
        'cloud_context' => $this->cloudContext,
        'ec2_method_name' => $method_name,
      ]);
    }
  }

}
