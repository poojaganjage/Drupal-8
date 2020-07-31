<?php

namespace Drupal\openstack\Service;

use Aws\Api\DateTimeResult;
use Aws\Credentials\CredentialProvider;
use Aws\Ec2\Ec2Client;
use Aws\MockHandler;
use Aws\Result;
use Drupal\aws_cloud\Service\Ec2\Ec2Service;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Session\AccountInterface;

/**
 * Interacts with OpenStack using the Amazon EC2 API.
 */
class OpenStackEc2Service extends Ec2Service {

  /**
   * TRUE to run the operation.  FALSE to run the operation in validation mode.
   *
   * @var bool
   */
  private $dryRun;

  /**
   * Constructs a new OpenStackEc2Service object.
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

    parent::__construct($entity_type_manager,
                        $config_factory,
                        $current_user,
                        $cloud_config_plugin_manager,
                        $field_type_plugin_manager,
                        $entity_field_manager,
                        $lock,
                        $queue_factory);

    // Setup the dryRun flag.
    $this->dryRun = (bool) $this->configFactory->get('openstack.settings')->get('openstack_test_mode');
  }

  /**
   * Get Ec2Client object for API execution.
   *
   * @param array $credentials
   *   The array of credentials.
   *
   * @return object
   *   The ec2 client.
   */
  protected function getEc2Client(array $credentials = []) {
    if (empty($credentials)) {
      $credentials = $this->cloudConfigPluginManager->loadCredentials();
    }

    try {
      $ec2_params = [
        'region' => $credentials['region'],
        'version' => $credentials['version'],
        'endpoint' => $credentials['endpoint'],
      ];

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

      $ec2_params['credentials'] = $provider;
      $ec2_client = new Ec2Client($ec2_params);
    }
    catch (\Exception $e) {
      $ec2_client = NULL;
      $this->logger('openstack_ec2_service')->error($e->getMessage());
    }
    $this->addMockHandler($ec2_client);
    return $ec2_client;
  }

  /**
   * Add a mock handler of aws sdk for testing.
   *
   * The mock data of openstack response is saved
   * in configuration "openstack_mock_data".
   *
   * @param \Aws\Ec2\Ec2Client $ec2_client
   *   The ec2 client.
   *
   * @see https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_handlers-and-middleware.html
   */
  private function addMockHandler(Ec2Client $ec2_client) {
    $mock_data = $this->configFactory->get('openstack.settings')->get('openstack_mock_data');
    if ($this->dryRun && $mock_data) {
      $func = static function ($command, $request) {
        $mock_data = \Drupal::service('config.factory')
          ->get('openstack.settings')
          ->get('openstack_mock_data');
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
        elseif ($command_name === 'DescribeAccountAttributes') {
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
        else {
          return new Result();
        }
      };

      // Set mock handler.
      $ec2_client->getHandlerList()->setHandler(new MockHandler([$func, $func]));
    }
  }

  /**
   * Execute the API of OpenStack EC2 Service.
   *
   * @param string $operation
   *   The operation to perform.
   * @param array $params
   *   An array of parameters.
   * @param array $credentials
   *   An array of credentials.
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
      \Drupal::moduleHandler()->invokeAll('openstack_pre_execute_alter', [
        &$params,
        $operation,
        $this->cloudContext,
      ]);

      $command = $ec2_client->getCommand($operation, $params);
      $results = $ec2_client->execute($command);

      // Let other modules alter the results before the module processes it.
      \Drupal::moduleHandler()->invokeAll('openstack_post_execute_alter', [
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
  public function updateInstances(array $params = [], $clear = TRUE) {
    $updated = FALSE;
    $entity_type = 'openstack_instance';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    // Call the api and get all instances.
    $result = $this->describeInstances($params);
    if ($result !== NULL) {
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
            OpenStackBatchOperations::class,
            'updateInstance',
          ], [$this->cloudContext, $instance]);

        }
      }
      $batch_builder->addOperation([
        OpenStackBatchOperations::class,
        'finished',
      ], [$entity_type, $stale, $clear]);
      $this->runBatch($batch_builder);
      $updated = TRUE;
    }
    return $updated;
  }

  /**
   * {@inheritdoc}
   */
  public function updateInstancesWithoutBatch(array $params = [], $clear = TRUE) {
    $updated = FALSE;
    $entity_type = 'openstack_instance';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    // Override to handle OpenStack specific instances.
    $results = $this->describeInstances($params);
    if ($results !== NULL) {
      $all_instances = $this->loadAllEntities($entity_type);

      $stale = [];
      // Make it easier to lookup the images by setting up
      // the array with the image_id.
      foreach ($all_instances ?: [] as $instance) {
        $stale[$instance->getInstanceId()] = $instance;
      }

      foreach ($results['Reservations'] ?: [] as $reservation) {
        foreach ($reservation['Instances'] ?: [] as $instance) {
          if (isset($stale[$instance['InstanceId']])) {
            unset($stale[$instance['InstanceId']]);
          }
          // Store the Reservation OwnerId in instance so batch
          // callback has access.
          $instance['reservation_ownerid'] = $reservation['OwnerId'];
          $instance['reservation_id'] = $reservation['ReservationId'];

          OpenStackBatchOperations::updateInstance($this->cloudContext, $instance);
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
   * {@inheritdoc}
   */
  public function updateImages(array $params = [], $clear = TRUE) {
    $updated = FALSE;
    $entity_type = 'openstack_image';
    $lock_name = $this->getLockKey($entity_type);
    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    // Load all entities by cloud_context.
    $image_entities = $this->entityTypeManager->getStorage($entity_type)->loadByProperties(
      ['cloud_context' => $this->cloudContext]
    );

    $result = $this->describeImages($params);

    if (isset($result)) {

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
          OpenStackBatchOperations::class,
          'updateImage',
        ], [$this->cloudContext, $image]);
      }

      $batch_builder->addOperation([
        OpenStackBatchOperations::class,
        'finished',
      ], [$entity_type, $stale, $clear]);
      $this->runBatch($batch_builder);
      $updated = is_array($result['Images']) ? count($result['Images']) : '';
    }
    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * {@inheritdoc}
   */
  public function updateKeyPairs() {
    $updated = FALSE;
    $entity_type = 'openstack_key_pair';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

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
          OpenStackBatchOperations::class,
          'updateKeyPair',
        ], [$this->cloudContext, $key_pair]);
      }

      $batch_builder->addOperation([
        OpenStackBatchOperations::class,
        'finished',
      ], [$entity_type, $stale, TRUE]);
      $this->runBatch($batch_builder);
      $updated = TRUE;
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * {@inheritdoc}
   */
  public function updateSecurityGroups(array $params = [], $clear = TRUE) {
    $updated = FALSE;
    $entity_type = 'openstack_security_group';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

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
          OpenStackBatchOperations::class,
          'updateSecurityGroup',
        ], [$this->cloudContext, $security_group]);
      }

      $batch_builder->addOperation([
        OpenStackBatchOperations::class,
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
  public function updateVolumes() {
    $updated = FALSE;
    $entity_type = 'openstack_volume';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

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
          OpenStackBatchOperations::class,
          'updateVolume',
        ], [$this->cloudContext, $volume, $snapshot_id_name_map]);
      }

      $batch_builder->addOperation([
        OpenStackBatchOperations::class,
        'finished',
      ], [$entity_type, $stale, TRUE]);
      $this->runBatch($batch_builder);
      $updated = TRUE;
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * {@inheritdoc}
   */
  public function updateSnapshots() {
    $updated = FALSE;
    $entity_type = 'openstack_snapshot';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

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
          OpenStackBatchOperations::class,
          'updateSnapshot',
        ], [$this->cloudContext, $snapshot]);
      }

      $batch_builder->addOperation([
        OpenStackBatchOperations::class,
        'finished',
      ], [$entity_type, $stale, TRUE]);
      $this->runBatch($batch_builder);
      $updated = TRUE;
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * {@inheritdoc}
   */
  public function updateNetworkInterfaces(array $params = [], $clear = TRUE) {
    $updated = FALSE;
    $entity_type = 'openstack_network_interface';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $result = $this->describeNetworkInterfaces($params);

    if (!empty($result)) {

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
        if (!empty($stale[$network_interface['NetworkInterfaceId']])) {
          unset($stale[$network_interface['NetworkInterfaceId']]);
        }
        $batch_builder->addOperation([
          OpenStackBatchOperations::class,
          'updateNetworkInterface',
        ], [$this->cloudContext, $network_interface]);
      }

      $batch_builder->addOperation([
        OpenStackBatchOperations::class,
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
  public function updateFloatingIp() {
    $updated = FALSE;
    $entity_type = 'openstack_floating_ip';
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

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
      $batch_builder = $this->initBatch('FloatingIp Update');

      foreach ($result['Addresses'] ?: [] as $floating_ip) {
        // Keep track of IPs that do not exist anymore
        // delete them after saving the rest of the IPs.
        if (isset($stale[$floating_ip['PublicIp']])) {
          unset($stale[$floating_ip['PublicIp']]);
        }
        $batch_builder->addOperation([
          OpenStackBatchOperations::class,
          'updateFloatingIp',
        ], [$this->cloudContext, $floating_ip]);
      }

      $batch_builder->addOperation([
        OpenStackBatchOperations::class,
        'finished',
      ], [$entity_type, $stale, TRUE]);
      $this->runBatch($batch_builder);
      $updated = TRUE;
    }

    $this->lock->release($lock_name);
    return $updated;
  }

  /**
   * Get Regions list from API.
   *
   * @param array $params
   *   The array of API parameters.
   * @param array $credentials
   *   The array of credentials.
   *
   * @return object
   *   The array of API result.
   */
  public function describeRegions(array $params = [], array $credentials = []) {
    $results = $this->execute('DescribeRegions', $params, $credentials);
    return $results;
  }

  /**
   * Launch Instance.
   *
   * @param array $params
   *   The array of API parameters.
   * @param array $tags
   *   The array of tags.
   */
  public function runInstances(array $params = [], array $tags = []) {
    $params += $this->getDefaultParameters();

    $results = $this->execute('RunInstances', $params);
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function clearAllEntities() {
    $timestamp = $this->getTimestamp();
    $this->clearEntities('openstack_instance', $timestamp);
    $this->clearEntities('openstack_security_group', $timestamp);
    $this->clearEntities('openstack_image', $timestamp);
    $this->clearEntities('openstack_network_interface', $timestamp);
    $this->clearEntities('openstack_floating_ip', $timestamp);
    $this->clearEntities('openstack_key_pair', $timestamp);
    $this->clearEntities('openstack_volume', $timestamp);
    $this->clearEntities('openstack_snapshot', $timestamp);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultParameters() {
    return [];
  }

  /**
   * Function will get instances that needs to be terminated.
   *
   * Query the openstack_instance table and return instances that are past a
   * certain timestamp.
   *
   * @return array
   *   An array of expired instance objects.
   */
  private function getExpiredInstances() {
    $expired_instances = [];
    /* @var \Drupal\Core\Entity\EntityStorageInterface $entity_storage $entity_storage */
    $entity_storage = $this->entityTypeManager->getStorage('openstack_instance');
    $entity_ids = $entity_storage
      ->getQuery()
      ->condition('termination_timestamp', time(), '<')
      ->condition('termination_timestamp', 0, '!=')
      ->condition('termination_timestamp', NULL, 'IS NOT NULL')
      ->condition('instance_state', ['running', 'stopped'], 'IN')
      ->condition('cloud_context', $this->cloudContext)
      ->execute();
    $entities = $entity_storage->loadMultiple($entity_ids);
    foreach ($entities ?: [] as $entity) {
      /* @var \Drupal\openstack\Entity\OpenStackInstance $entity */
      $expired_instances['InstanceIds'][] = $entity->getInstanceId();
    }
    return $expired_instances;
  }

  /**
   * {@inheritdoc}
   */
  public function terminateExpiredInstances() {
    // Terminate instances past the timestamp.
    $instances = $this->getExpiredInstances($this->cloudContext);
    if (!empty($instances)) {
      $this->logger('openstack')->notice(
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
    $entity_storage = $this->entityTypeManager->getStorage('openstack_image');
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
    $update_resources_queue = $this->queueFactory->get('openstack_update_resources_queue');
    $method_names = [
      'terminateExpiredInstances',
      'updateInstances',
      'updateImages',
      'updateKeyPairs',
      'updateSecurityGroups',
      'updateVolumes',
      'updateSnapshots',
      'updateNetworkInterfaces',
      'updateFloatingIp',
      'updatePendingImages',
    ];
    foreach ($method_names as $method_name) {
      $update_resources_queue->createItem([
        'cloud_context' => $this->cloudContext,
        'openstack_ec2_method_name' => $method_name,
      ]);
    }
  }

}
