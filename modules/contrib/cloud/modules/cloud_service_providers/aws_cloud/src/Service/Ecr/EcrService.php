<?php

namespace Drupal\aws_cloud\Service\Ecr;

use Aws\Credentials\AssumeRoleCredentialProvider;
use Aws\Credentials\CredentialProvider;
use Aws\Ecr\EcrClient;
use Aws\Ecr\Exception\EcrException;
use Aws\MockHandler;
use Aws\Result;
use Aws\Sts\StsClient;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Service\CloudServiceBase;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class EcrService.
 */
class EcrService extends CloudServiceBase implements EcrServiceInterface {

  /**
   * Cloud context string.
   *
   * @var string
   */
  private $cloudContext;

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
  private $cloudConfigPluginManager;

  /**
   * TRUE to run the operation, FALSE to run the operation in validation mode.
   *
   * @var bool
   */
  private $dryRun;

  /**
   * Constructs a new S3Service object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager) {

    // The parent constructor takes care of $this->messenger object.
    parent::__construct();

    // Setup the configuration factory.
    $this->configFactory = $config_factory;

    // Setup the dryRun flag.
    $this->dryRun = (bool) $this->configFactory->get('aws_cloud.settings')->get('aws_cloud_test_mode');

    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function setCloudContext($cloud_context) {
    $this->cloudContext = $cloud_context;
    $this->cloudConfigPluginManager->setCloudContext($cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthorizationToken(array $params = []) {
    $results = $this->execute('GetAuthorizationToken', $params);
    $token = FALSE;
    if (isset($results)) {
      foreach ($results['authorizationData'] ?: [] as $data) {
        $token = $data['authorizationToken'];
        break;
      }
    }
    return $token;
  }

  /**
   * {@inheritdoc}
   */
  public function createRepository($name) {
    $results = $this->execute('CreateRepository', [
      'repositoryName' => $name,
    ]);
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function describeImages(array $params = []) {
    return $this->execute('DescribeImages', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeRepositories(array $params = []) {
    return $this->execute('DescribeRepositories', $params);
  }

  /**
   * {@inheritdoc}
   */
  public function doesRepositoryExists($name) {
    $exists = FALSE;
    $results = $this->describeRepositories([
      'repositoryNames' => [
        $name,
      ],
    ]);
    if (isset($results)) {
      foreach ($results['repositories'] ?: [] as $repo) {
        if ($repo['repositoryName'] === $name) {
          $exists = TRUE;
          break;
        }
        break;
      }
    }
    return $exists;
  }

  /**
   * {@inheritdoc}
   */
  public function doesImageExist($name, $tag) {
    $exist = FALSE;
    $image = $this->describeImages([
      'repositoryName' => $name,
      'imageIds' => [
        [
          'imageTag' => $tag,
        ],
      ],
    ]);
    if ($image !== NULL) {
      $exist = TRUE;
    }
    return $exist;
  }

  /**
   * {@inheritdoc}
   */
  public function getEcrEndpoint() {
    $this->cloudConfigPluginManager->setCloudContext($this->cloudContext);
    $cloud_config = $this->cloudConfigPluginManager->loadConfigEntity();
    $account_id = $cloud_config->get('field_account_id')->value;

    // Use the switch role account_id if switching is enabled.
    $use_assume_role = $cloud_config->get('field_use_assume_role')->value ?? FALSE;
    $use_switch_role = $cloud_config->get('field_use_switch_role')->value ?? FALSE;
    if (!empty($use_assume_role) && !empty($use_switch_role)) {
      $account_id = trim($cloud_config->get('field_switch_role_account_id')->value);
    }
    return $account_id . '.dkr.ecr.' . $cloud_config->get('field_region')->value . '.amazonaws.com';
  }

  /**
   * Execute the API of AWS Cloud ECR Service.
   *
   * @param string $operation
   *   The operation to perform.
   * @param array $params
   *   An array of parameters.
   *
   * @return array
   *   Array of execution result or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ecr\EcrServiceException
   *   Thrown when $ecr_client (EcrClient) is NULL.
   */
  private function execute($operation, array $params = []) {
    $results = NULL;

    $ecr_client = $this->getEcrClient();
    if ($ecr_client === NULL) {
      throw new EcrServiceException('No ECR Client found. Cannot perform API operations');
    }

    try {
      // Let other modules alter the parameters
      // before they are sent through the API.
      \Drupal::moduleHandler()->invokeAll('aws_cloud_pre_execute_alter', [
        &$params,
        $operation,
        $this->cloudContext,
      ]);

      $command = $ecr_client->getCommand($operation, $params);
      $results = $ecr_client->execute($command);

      // Let other modules alter the results before the module processes it.
      \Drupal::moduleHandler()->invokeAll('aws_cloud_post_execute_alter', [
        &$results,
        $operation,
        $this->cloudContext,
      ]);
    }
    catch (EcrException $e) {
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
      // Do not need to log this.
      if ($e->getAwsErrorCode() !== 'RepositoryNotFoundException') {
        $this->handleException($e);
      }
    }
    return $results;
  }

  /**
   * Load and return an S3Client.
   */
  private function getEcrClient() {
    // Use the plugin manager to load the aws credentials.
    $credentials = $this->cloudConfigPluginManager->loadCredentials();

    try {
      $ecr_params = [
        'region' => $credentials['region'],
        'version' => $credentials['version'],
      ];
      $provider = FALSE;

      // Load credentials if needed.
      if (empty($credentials['use_instance_profile'])) {
        $provider = CredentialProvider::ini('default', $credentials['ini_file']);
        $provider = CredentialProvider::memoize($provider);
      }

      if (!empty($credentials['use_assume_role'])) {
        // Assume role.
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
            'RoleSessionName' => 'ecr_client_assume_role',
          ],
        ]);

        // Memoize takes care of re-authenticating when the tokens expire.
        $assumeRoleCredentials = CredentialProvider::memoize($assumeRoleCredentials);
        $ecr_params = [
          'region' => $credentials['region'],
          'version' => $credentials['version'],
          'credentials' => $assumeRoleCredentials,
        ];
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
              'RoleSessionName' => 'ecr_client_switch_role',
            ],
          ]);
          $switchRoleCredentials = CredentialProvider::memoize($switchRoleCredentials);
          $ecr_params['credentials'] = $switchRoleCredentials;
        }
      }
      elseif ($provider !== FALSE) {
        $ecr_params['credentials'] = $provider;
      }

      $ecr_client = new EcrClient($ecr_params);
    }
    catch (\Exception $e) {
      $ecr_client = NULL;
      $this->logger('ecr_service')->error($e->getMessage());
    }
    $this->addMockHandler($ecr_client);
    return $ecr_client;
  }

  /**
   * Add a mock handler of aws sdk for testing.
   *
   * The mock data of aws response is saved
   * in configuration "aws_cloud_mock_data".
   *
   * @param \Aws\Ecr\EcrClient $ecr_client
   *   The ECR client.
   *
   * @see https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_handlers-and-middleware.html
   */
  private function addMockHandler(EcrClient $ecr_client) {
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
          return new Result($result_data);
        }
        else {
          return new Result();
        }
      };

      // Set mock handler.
      $ecr_client->getHandlerList()->setHandler(new MockHandler([$func, $func]));
    }
  }

}
