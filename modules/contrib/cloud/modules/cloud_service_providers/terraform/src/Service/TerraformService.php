<?php

namespace Drupal\terraform\Service;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Service\CloudServiceBase;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Queue\QueueFactory;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class TerraformService.
 */
class TerraformService extends CloudServiceBase implements TerraformServiceInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * Guzzle client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

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
   * Constructs a new TerraformService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   An entity type manager instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory.
   * @param \Drupal\Core\Http\ClientFactory $client_factory
   *   The http client.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock interface.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory,
                              ClientFactory $client_factory,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
                              LockBackendInterface $lock,
                              QueueFactory $queue_factory) {

    // The parent constructor takes care of $this->messenger object.
    parent::__construct();

    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->httpClient = $client_factory->fromOptions([
      'base_uri' => 'https://app.terraform.io/api/v2/',
    ]);
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
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
   * {@inheritdoc}
   */
  public function describeWorkspaces(array $params = []) {
    $credentials = $this->cloudConfigPluginManager->loadCredentials();
    $organization = $credentials['organization'];
    if (empty($params['name'])) {
      return $this->request('get', "organizations/$organization/workspaces");
    }
    else {
      return [$this->request('get', "organizations/$organization/workspaces/" . $params['name'])];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createWorkspace(array $params) {
    $credentials = $this->cloudConfigPluginManager->loadCredentials();
    $organization = $credentials['organization'];
    $body = [
      'data' => [
        'attributes' => [
          'name' => $params['name'],
          'vcs-repo' => [
            'identifier' => $params['vcs_repo_identifier'],
            'oauth-token-id' => $params['oauth_token_id'],
            'branch' => $params['vcs_repo_branch'] ?? '',
          ],
        ],
      ],
      'type' => 'workspaces',
    ];
    return $this->request('post', "organizations/$organization/workspaces", ['json' => $body]);
  }

  /**
   * {@inheritdoc}
   */
  public function patchWorkspace(array $params) {
    $body = [
      'data' => [
        'attributes' => [
          'vcs-repo' => [
            'branch' => $params['vcs_repo_branch'] ?? '',
          ],
        ],
        'type' => 'workspaces',
      ],
    ];

    return $this->request('patch', 'workspaces/' . $params['workspace_id'], ['json' => $body]);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteWorkspace($name) {
    $credentials = $this->cloudConfigPluginManager->loadCredentials();
    $organization = $credentials['organization'];
    return $this->request('delete', "organizations/$organization/workspaces/$name");
  }

  /**
   * {@inheritdoc}
   */
  public function updateWorkspaces(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'terraform_workspace',
      'Workspace',
      'describeWorkspaces',
      'updateWorkspace',
      $params,
      $clear
    );
  }

  /**
   * {@inheritdoc}
   */
  public function createRun(array $params) {
    $credentials = $this->cloudConfigPluginManager->loadCredentials();
    $organization = $credentials['organization'];
    $body = [
      'data' => [
        'attributes' => [
          'message' => $params['message'],
          'is-destroy' => $params['is_destroy'],
        ],
        'type' => 'runs',
        'relationships' => [
          'workspace' => [
            'data' => [
              'type' => 'workspaces',
              'id' => $params['workspace_id'],
            ],
          ],
        ],
      ],
    ];

    return $this->request('post', "runs", ['json' => $body]);
  }

  /**
   * {@inheritdoc}
   */
  public function describeRuns(array $params = []) {
    $workspace_id = $params['terraform_workspace']->getWorkspaceId();
    return $this->request('get', "workspaces/$workspace_id/runs");
  }

  /**
   * {@inheritdoc}
   */
  public function applyRun(array $params = []) {
    $run_id = $params['terraform_run']->getRunId();
    $body = [
      'comment' => 'Looks good to me',
    ];
    return $this->request('post', "runs/$run_id/actions/apply", ['json' => $body]);
  }

  /**
   * {@inheritdoc}
   */
  public function updateRunLogs(array $params = []) {
    $terraform_run = $params['terraform_run'];

    // Plan.
    if (!empty($terraform_run->getPlanId())) {
      $plan = $this->showPlan($terraform_run->getPlanId());
      if (!empty($plan['attributes']['log-read-url'])) {
        $terraform_run->setPlanLog(file_get_contents($plan['attributes']['log-read-url']));
      }
    }

    // Apply.
    if (!empty($terraform_run->getApplyId())) {
      $apply = $this->showApply($terraform_run->getApplyId());
      if (!empty($apply['attributes']['log-read-url'])) {
        $terraform_run->setApplyLog(file_get_contents($apply['attributes']['log-read-url']));
      }
    }

    $terraform_run->save();
  }

  /**
   * {@inheritdoc}
   */
  public function showPlan($plan_id) {
    return $this->request('get', "plans/$plan_id");
  }

  /**
   * {@inheritdoc}
   */
  public function showApply($apply_id) {
    return $this->request('get', "applies/$apply_id");
  }

  /**
   * {@inheritdoc}
   */
  public function describeStates(array $params = []) {
    $credentials = $this->cloudConfigPluginManager->loadCredentials();
    $organization = $credentials['organization'];
    $workspace = $params['terraform_workspace'];
    $params = [
      'query' => [
        'filter[workspace][name]' => $workspace->getName(),
        'filter[organization][name]' => $organization,
      ],
    ];

    return $this->request('get', "state-versions", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function describeVariables(array $params = []) {
    $credentials = $this->cloudConfigPluginManager->loadCredentials();
    $organization = $credentials['organization'];
    $workspace = $params['terraform_workspace'];
    $params = [
      'query' => [
        'filter[workspace][name]' => $workspace->getName(),
        'filter[organization][name]' => $organization,
      ],
    ];

    return $this->request('get', "vars", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function createVariable(array $params) {
    $credentials = $this->cloudConfigPluginManager->loadCredentials();
    $organization = $credentials['organization'];
    $body = [
      'data' => [
        'attributes' => [
          'key' => $params['key'],
          'value' => $params['value'],
          'description' => $params['description'],
          'category' => $params['category'],
          'hcl' => $params['hcl'],
          'sensitive' => $params['sensitive'],
        ],
        'type' => 'vars',
        'relationships' => [
          'workspace' => [
            'data' => [
              'type' => 'workspaces',
              'id' => $params['workspace_id'],
            ],
          ],
        ],
      ],
    ];

    return $this->request('post', "vars", ['json' => $body]);
  }

  /**
   * {@inheritdoc}
   */
  public function patchVariable(array $params) {
    $body = [
      'data' => [
        'id' => $params['variable_id'],
        'attributes' => [],
        'type' => 'vars',
      ],
    ];

    foreach (['key', 'value', 'description', 'category', 'hcl', 'sensitive'] as $attribute) {
      if (!empty($params[$attribute])) {
        $body['data']['attributes'][$attribute] = $params[$attribute];
      }
    }

    return $this->request('patch', 'vars/' . $params['variable_id'], ['json' => $body]);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteVariable($name) {
    return $this->request('delete', "vars/$name");
  }

  /**
   * {@inheritdoc}
   */
  public function updateRuns(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'terraform_run',
      'Run',
      'describeRuns',
      'updateRun',
      $params,
      $clear
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateStates(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'terraform_state',
      'State',
      'describeStates',
      'updateState',
      $params,
      $clear
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateVariables(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'terraform_variable',
      'Variable',
      'describeVariables',
      'updateVariable',
      $params,
      $clear
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateAllRuns() {
    $terraform_workspaces = $this->loadAllEntities('terraform_workspace');
    foreach ($terraform_workspaces as $terraform_workspace) {
      $this->updateRuns([
        'terraform_workspace' => $terraform_workspace,
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateAllStates() {
    $terraform_workspaces = $this->loadAllEntities('terraform_workspace');
    foreach ($terraform_workspaces as $terraform_workspace) {
      $this->updateStates([
        'terraform_workspace' => $terraform_workspace,
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateAllVariables() {
    $terraform_workspaces = $this->loadAllEntities('terraform_workspace');
    foreach ($terraform_workspaces as $terraform_workspace) {
      $this->updateVariables([
        'terraform_workspace' => $terraform_workspace,
      ]);
    }
  }

  /**
   * Helper method to do a Guzzle HTTP Request.
   *
   * @param string $http_method
   *   The http method in lower case.
   * @param string $endpoint
   *   The endpoint to access.
   * @param array $params
   *   Parameters to pass to the request.
   *
   * @return array
   *   Array interpretation of the response body.
   */
  private function request($http_method, $endpoint, array $params = []) {
    $output = NULL;
    try {
      $params = array_merge_recursive($this->getDefaultParams(), $params);
      $response = $this->httpClient->$http_method(
        $endpoint,
        $params
      );
      $output = $response->getBody()->getContents();
    }
    catch (GuzzleException $error) {
      $response = $error->getResponse();
      $response_info = $response->getBody()->getContents();
      $message = new FormattableMarkup(
        'Terraform API error. Error details are as follows:<pre>@response</pre>',
        [
          '@response' => print_r(json_decode($response_info), TRUE),
        ]
      );
      $this->logError('Remote API Connection', $error, $message);
    }
    catch (\Exception $error) {
      $this->logError('Remote API Connection', $error, $this->t('An unknown error
      occurred while trying to connect to the remote API. This is not a Guzzle
      error, nor an error in the remote API, rather a generic local error
      occurred. The reported error was @error',
        ['@error' => $error->getMessage()]
      ));
    }

    return empty($output) ? [] : json_decode($output, TRUE)['data'];
  }

  /**
   * Create queue items for update resources queue.
   */
  public function createResourceQueueItems() {
    $update_resources_queue = $this->queueFactory->get('terraform_update_resources_queue');
    $method_names = [
      'updateWorkspaces',
      'updateAllRuns',
      'updateAllStates',
      'updateAllVariables',
    ];
    foreach ($method_names as $method_name) {
      $update_resources_queue->createItem([
        'cloud_context' => $this->cloudContext,
        'terraform_method_name' => $method_name,
      ]);
    }
  }

  /**
   * Log errors to watchdog and throw an exception.
   *
   * @param string $type
   *   The error type.
   * @param \Exception $exception
   *   The exception object.
   * @param string $message
   *   The message to log.
   * @param bool $throw
   *   TRUE to throw exception.
   *
   * @throws \Drupal\terraform\Service\TerraformServiceException
   */
  private function logError($type, \Exception $exception, $message, $throw = TRUE) {
    watchdog_exception($type, $exception, $message);
    if ($throw === TRUE) {
      throw new TerraformServiceException($exception);
    }
  }

  /**
   * Setup any default parameters for the Guzzle request.
   *
   * @return array
   *   Array of parameters.
   */
  private function getDefaultParams() {
    $credentials = $this->cloudConfigPluginManager->loadCredentials();
    $params = [
      'headers' => [
        'Content-Type' => 'application/vnd.api+json',
        'Authorization' => 'Bearer ' . $credentials['api_token'],
      ],
    ];
    return $params;
  }

  /**
   * Update entities.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $entity_type_label
   *   The entity type label.
   * @param string $get_entities_method
   *   The method name of get entities.
   * @param string $update_entity_method
   *   The method name of update entity.
   * @param array $params
   *   The params for API Call.
   * @param bool $clear
   *   TRUE to clear stale entities.
   * @param bool $batch_mode
   *   Whether updating entities in batch.
   *
   * @return bool
   *   True or false depending on lock name.
   *
   * @throws \Drupal\terraform\Service\TerraformServiceException
   *   Thrown when unable to get get_entities_method.
   */
  private function updateEntities(
    $entity_type,
    $entity_type_label,
    $get_entities_method,
    $update_entity_method,
    array $params = [],
    $clear = TRUE,
    $batch_mode = TRUE
  ) {
    $updated = FALSE;
    $lock_name = $this->getLockKey($entity_type);

    if (!$this->lock->acquire($lock_name)) {
      return FALSE;
    }

    $result = NULL;
    try {
      $result = $this->$get_entities_method($params);
    }
    catch (TerraformServiceException $e) {
      $this->logger('terraform_service')->error($e->getMessage());
    }

    if ($result !== NULL) {
      $conditions = [];
      if (!empty($params['terraform_workspace'])) {
        $conditions['terraform_workspace_id'] = $params['terraform_workspace']->id();
      }
      $all_entities = $this->loadAllEntities($entity_type, $conditions);
      $stale = [];
      foreach ($all_entities ?: [] as $entity) {
        $key = $entity->getName();
        $stale[$key] = $entity;
      }

      if ($batch_mode) {
        /* @var \Drupal\Core\Batch\BatchBuilder $batch_builder */
        $batch_builder = $this->initBatch("$entity_type_label Update");
      }

      foreach ($result ?: [] as $entity) {
        if ($entity_type === 'terraform_workspace') {
          $key = $entity['attributes']['name'];
        }
        else {
          $key = $entity['id'];
        }

        if (isset($stale[$key])) {
          unset($stale[$key]);
        }

        if ($batch_mode) {
          $batch_params = [$this->cloudContext, $entity];
          if (!empty($params['terraform_workspace'])) {
            $batch_params[] = $params['terraform_workspace']->id();
          }

          $batch_builder->addOperation([
            TerraformBatchOperations::class,
            $update_entity_method,
          ], $batch_params);
        }
        else {
          if (empty($params['terraform_workspace'])) {
            TerraformBatchOperations::$update_entity_method($this->cloudContext, $entity);
          }
          else {
            TerraformBatchOperations::$update_entity_method($this->cloudContext, $entity, $params['terraform_workspace']->id());
          }
        }
      }

      if ($batch_mode) {
        $batch_builder->addOperation([
          TerraformBatchOperations::class,
          'finished',
        ], [$entity_type, $stale, $clear]);
        $this->runBatch($batch_builder);
      }
      else {
        if (count($stale) && $clear === TRUE) {
          $this->entityTypeManager->getStorage($entity_type)->delete($stale);
        }
      }

      $updated = TRUE;
    }

    $this->lock->release($lock_name);
    return $updated;
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
    $start = time();
    $batch_array = $batch_builder->toArray();
    batch_set($batch_array);

    // Reset the progressive so batch works with out a web head.
    $batch = &batch_get();
    $batch['progressive'] = FALSE;
    batch_process();

    // Log the end time.
    $end = time();
    $this->logger('terraform_service')->info(
      $this->t('@updater - @cloud_context: Batch operation took @time seconds.',
        [
          '@cloud_context' => $this->cloudContext,
          '@updater' => $batch_array['title'],
          '@time' => $end - $start,
        ]
      )
    );
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
   * {@inheritdoc}
   */
  public function clearAllEntities() {
    $timestamp = time();
    $this->clearEntities('terraform_workspace', $timestamp);
    $this->clearEntities('terraform_run', $timestamp);
    $this->clearEntities('terraform_state', $timestamp);
  }

}
