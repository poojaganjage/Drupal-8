<?php

namespace Drupal\k8s\Service;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Service\CloudServiceBase;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Html;
use Drupal\k8s\Service\K8sClientExtension\K8sClient;
use Drupal\k8s\Service\K8sClientExtension\K8sRepositoryRegistry;
use Drupal\k8s\Service\K8sClientExtension\Models\K8sApiServiceModel;
use Drupal\k8s\Service\K8sClientExtension\Models\K8sClusterRoleBindingModel;
use Drupal\k8s\Service\K8sClientExtension\Models\K8sClusterRoleModel;
use Drupal\k8s\Service\K8sClientExtension\Models\K8sLimitRangeModel;
use Drupal\k8s\Service\K8sClientExtension\Models\K8sPriorityClassModel;
use Drupal\k8s\Service\K8sClientExtension\Models\K8sRoleBindingModel;
use Drupal\k8s\Service\K8sClientExtension\Models\K8sRoleModel;
use Drupal\k8s\Service\K8sClientExtension\Models\K8sServiceAccountModel;
use Drupal\k8s\Service\K8sClientExtension\Models\K8sStatefulSetModel;
use Drupal\k8s\Service\K8sClientExtension\Models\K8sStorageClassModel;
use Maclof\Kubernetes\Models\ConfigMap;
use Maclof\Kubernetes\Models\CronJob;
use Maclof\Kubernetes\Models\DaemonSet;
use Maclof\Kubernetes\Models\Deployment;
use Maclof\Kubernetes\Models\Endpoint;
use Maclof\Kubernetes\Models\Ingress;
use Maclof\Kubernetes\Models\Job;
use Maclof\Kubernetes\Models\NamespaceModel;
use Maclof\Kubernetes\Models\NetworkPolicy;
use Maclof\Kubernetes\Models\PersistentVolume;
use Maclof\Kubernetes\Models\PersistentVolumeClaim;
use Maclof\Kubernetes\Models\Pod;
use Maclof\Kubernetes\Models\QuotaModel;
use Maclof\Kubernetes\Models\ReplicaSet;
use Maclof\Kubernetes\Models\Secret;
use Maclof\Kubernetes\Models\Service;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * K8sService service interacts with the K8s API.
 */
class K8sService extends CloudServiceBase implements K8sServiceInterface {

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
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new K8sService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   An entity type manager instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager.
   * @param \Drupal\core\Field\FieldTypePluginManagerInterface $field_type_plugin_manager
   *   The field type plugin manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock interface.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory,
                              AccountInterface $current_user,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
                              FieldTypePluginManagerInterface $field_type_plugin_manager,
                              EntityFieldManagerInterface $entity_field_manager,
                              LockBackendInterface $lock,
                              QueueFactory $queue_factory,
                              ModuleHandlerInterface $module_handler) {

    // The parent constructor takes care of $this->messenger object.
    parent::__construct();

    // Setup the entity type manager for querying entities.
    $this->entityTypeManager = $entity_type_manager;

    // Setup the configuration factory.
    $this->configFactory = $config_factory;

    $this->currentUser = $current_user;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
    $this->fieldTypePluginManager = $field_type_plugin_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->lock = $lock;
    $this->queueFactory = $queue_factory;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function setCloudContext($cloud_context) {
    $this->cloudContext = $cloud_context;
    $this->cloudConfigPluginManager->setCloudContext($cloud_context);
  }

  /**
   * Load and return an Client.
   *
   * @param string $namespace
   *   The namespace.
   *
   * @return object
   *   The value of created object.
   */
  protected function getClient($namespace = '') {
    $client = NULL;
    $credentials = $this->cloudConfigPluginManager->loadCredentials();
    try {
      $client = new K8sClient(
        [
          'master' => $credentials['master'],
          'token' => $credentials['token'],
          'verify' => FALSE,
          'namespace' => $namespace,
        ],
        NULL,
        new K8sRepositoryRegistry()
      );
    }
    catch (\Exception $e) {
      $this->logger('k8s_service')->error($e->getMessage());
    }

    return $client;
  }

  /**
   * {@inheritdoc}
   */
  public function getPods(array $params = []) {
    return $this->getClient()
      ->pods()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createPod($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->pods()
      ->create(new Pod($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updatePod($namespace, array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    return $this->getClient($namespace)
      ->pods()
      ->update(new Pod($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deletePod($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->pods()
      ->delete(new Pod($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getPodLogs($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->pods()
      ->logs(new Pod($params), [
        'pretty' => TRUE,
      ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getNodes(array $params = []) {
    return $this->getClient()
      ->nodes()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function getNamespaces(array $params = []) {
    return $this->getClient()
      ->namespaces()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createNamespace(array $params = []) {
    return $this->getClient()
      ->namespaces()
      ->create(new NamespaceModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateNamespace(array $params = []) {
    return $this->getClient()
      ->namespaces()
      ->update(new NamespaceModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteNamespace(array $params = []) {
    return $this->getClient()
      ->namespaces()
      ->delete(new NamespaceModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getMetricsPods(array $params = []) {
    return $this->getClient()
      ->metricsPods()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function getMetricsNodes(array $params = []) {
    return $this->getClient()
      ->metricsNodes()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function updatePods(array $params = [], $clear = TRUE) {
    $metrics = $this->getPodsMetricsMap();
    return $this->updateEntities(
      'k8s_pod',
      'Pod',
      'getPods',
      'updatePod',
      $params,
      $clear,
      'namespace',
      TRUE,
      $metrics
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updatePodsWithoutBatch(array $params = [], $clear = TRUE) {
    $metrics = $this->getPodsMetricsMap();
    return $this->updateEntities(
      'k8s_pod',
      'Pod',
      'getPods',
      'updatePod',
      $params,
      $clear,
      'namespace',
      FALSE,
      $metrics
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateNodes(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_node',
      'Node',
      'getNodes',
      'updateNode',
      $params,
      $clear
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateNodesWithoutBatch(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_node',
      'Node',
      'getNodes',
      'updateNode',
      $params,
      $clear,
      NULL,
      FALSE
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateNamespaces(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_namespace',
      'Namespace',
      'getNamespaces',
      'updateNamespace',
      $params,
      $clear
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateNamespacesWithoutBatch(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_namespace',
      'Namespace',
      'getNamespaces',
      'updateNamespace',
      $params,
      $clear,
      NULL,
      FALSE
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateDeployments(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_deployment',
      'Deployment',
      'getDeployments',
      'updateDeployment',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateReplicaSets(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_replica_set',
      'ReplicaSet',
      'getReplicaSets',
      'updateReplicaSet',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateServices(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_service',
      'Service',
      'getServices',
      'updateService',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateCronJobs(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_cron_job',
      'CronJob',
      'getCronJobs',
      'updateCronJob',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateJobs(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_job',
      'Job',
      'getJobs',
      'updateJob',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateResourceQuotas(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_resource_quota',
      'Resource Quota',
      'getResourceQuotas',
      'updateResourceQuota',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateLimitRanges(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_limit_range',
      'Limit Range',
      'getLimitRanges',
      'updateLimitRange',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateSecrets(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_secret',
      'Secret',
      'getSecrets',
      'updateSecret',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateConfigMaps(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_config_map',
      'ConfigMap',
      'getConfigMaps',
      'updateConfigMap',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateRoles(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_role',
      'Role',
      'getRoles',
      'updateRole',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateClusterRoles(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_cluster_role',
      'ClusterRole',
      'getClusterRoles',
      'updateClusterRole',
      $params,
      $clear
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateStorageClasses(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_storage_class',
      'StorageClass',
      'getStorageClasses',
      'updateStorageClass',
      $params,
      $clear
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updatePersistentVolumes(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_persistent_volume',
      'PersistentVolume',
      'getPersistentVolumes',
      'updatePersistentVolume',
      $params,
      $clear,
      NULL,
      FALSE
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateVolumeWithoutBatch(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_persistent_volume',
      'PersistentVolume',
      'getPersistentVolumes',
      'updatePersistentVolume',
      $params,
      $clear,
      NULL,
      FALSE
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateStatefulSets(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_stateful_set',
      'StatefulSet',
      'getStatefulSets',
      'updateStatefulSet',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateIngresses(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_ingress',
      'Ingress',
      'getIngresses',
      'updateIngress',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateDaemonSets(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_daemon_set',
      'DaemonSet',
      'getDaemonSets',
      'updateDaemonSet',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateEndpoints(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_endpoint',
      'Endpoint',
      'getEndpoints',
      'updateEndpoint',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updatePersistentVolumeClaims(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_persistent_volume_claim',
      'PersistentVolumeClaim',
      'getPersistentVolumeClaims',
      'updatePersistentVolumeClaim',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateClusterRoleBindings(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_cluster_role_binding',
      'ClusterRoleBinding',
      'getClusterRoleBindings',
      'updateClusterRoleBinding',
      $params,
      $clear
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateApiServices(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_api_service',
      'ApiService',
      'getApiServices',
      'updateApiService',
      $params,
      $clear
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateRoleBindings(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_role_binding',
      'RoleBinding',
      'getRoleBindings',
      'updateRoleBinding',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateServiceAccounts(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_service_account',
      'ServiceAccount',
      'getServiceAccounts',
      'updateServiceAccount',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updatePriorityClasses(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_priority_class',
      'PriorityClass',
      'getPriorityClasses',
      'updatePriorityClass',
      $params,
      $clear
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDeployments(array $params = []) {
    return $this->getClient()
      ->deployments()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createDeployment($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->deployments()
      ->create(new Deployment($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateDeployment($namespace, array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    return $this->getClient($namespace)
      ->deployments()
      ->update(new Deployment($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteDeployment($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->deployments()
      ->delete(new Deployment($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getReplicaSets(array $params = []) {
    return $this->getClient()
      ->replicaSets()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createReplicaSet($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->replicaSets()
      ->create(new ReplicaSet($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateReplicaSet($namespace, array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    return $this->getClient($namespace)
      ->replicaSets()
      ->update(new ReplicaSet($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteReplicaSet($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->replicaSets()
      ->delete(new ReplicaSet($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getServices(array $params = []) {
    return $this->getClient()
      ->services()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createService($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->services()
      ->create(new Service($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateService($namespace, array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    return $this->getClient($namespace)
      ->services()
      ->update(new Service($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteService($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->services()
      ->delete(new Service($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getCronJobs(array $params = []) {
    return $this->getClient()
      ->cronJobs()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createCronJob($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->cronJobs()
      ->create(new CronJob($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateCronJob($namespace, array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    return $this->getClient($namespace)
      ->cronJobs()
      ->update(new CronJob($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteCronJob($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->cronJobs()
      ->delete(new CronJob($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getJobs(array $params = []) {
    return $this->getClient()
      ->jobs()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createJob($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->jobs()
      ->create(new Job($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateJob($namespace, array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    return $this->getClient($namespace)
      ->jobs()
      ->update(new Job($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteJob($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->jobs()
      ->delete(new Job($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceQuotas(array $params = []) {
    return $this->getClient()
      ->quotas()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createResourceQuota($namespace, array $params = []) {
    $params['kind'] = 'ResourceQuota';
    return $this->getClient($namespace)
      ->quotas()
      ->create(new QuotaModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateResourceQuota($namespace, array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    $params['kind'] = 'ResourceQuota';

    return $this->getClient($namespace)
      ->quotas()
      ->update(new QuotaModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteResourceQuota($namespace, array $params = []) {
    $params['kind'] = 'ResourceQuota';

    return $this->getClient($namespace)
      ->quotas()
      ->delete(new QuotaModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getLimitRanges(array $params = []) {
    return $this->getClient()
      ->limitRanges()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createLimitRange($namespace, array $params = []) {
    $params['kind'] = 'LimitRange';
    return $this->getClient($namespace)
      ->limitRanges()
      ->create(new K8sLimitRangeModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateLimitRange($namespace, array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    $params['kind'] = 'LimitRange';

    return $this->getClient($namespace)
      ->limitRanges()
      ->update(new K8sLimitRangeModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteLimitRange($namespace, array $params = []) {
    $params['kind'] = 'LimitRange';

    return $this->getClient($namespace)
      ->limitRanges()
      ->delete(new K8sLimitRangeModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getSecrets(array $params = []) {
    return $this->getClient()
      ->secrets()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createSecret($namespace, array $params = []) {
    $params['kind'] = 'Secret';
    return $this->getClient($namespace)
      ->secrets()
      ->create(new Secret($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateSecret($namespace, array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    $params['kind'] = 'Secret';

    return $this->getClient($namespace)
      ->secrets()
      ->update(new Secret($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteSecret($namespace, array $params = []) {
    $params['kind'] = 'Secret';

    return $this->getClient($namespace)
      ->secrets()
      ->delete(new Secret($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigMaps(array $params = []) {
    return $this->getClient()
      ->configMaps()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigMap($namespace, array $params = []) {
    $params['kind'] = 'ConfigMap';
    return $this->getClient($namespace)
      ->configMaps()
      ->create(new ConfigMap($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateConfigMap($namespace, array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    $params['kind'] = 'ConfigMap';

    return $this->getClient($namespace)
      ->configMaps()
      ->update(new ConfigMap($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteConfigMap($namespace, array $params = []) {
    $params['kind'] = 'ConfigMap';

    return $this->getClient($namespace)
      ->configMaps()
      ->delete(new ConfigMap($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateNetworkPolicies(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_network_policy',
      'Network Policy',
      'getNetworkPolicies',
      'updateNetworkPolicy',
      $params,
      $clear,
      'namespace'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getNetworkPolicies(array $params = []) {
    return $this->getClient()
      ->networkPolicies()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createNetworkPolicy($namespace, array $params = []) {
    $params['kind'] = 'NetworkPolicy';
    return $this->getClient($namespace)
      ->networkPolicies()
      ->create(new NetworkPolicy($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateNetworkPolicy($namespace, array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    $params['kind'] = 'NetworkPolicy';

    return $this->getClient($namespace)
      ->networkPolicies()
      ->update(new NetworkPolicy($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteNetworkPolicy($namespace, array $params = []) {
    $params['kind'] = 'NetworkPolicy';

    return $this->getClient($namespace)
      ->networkPolicies()
      ->delete(new NetworkPolicy($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getRoles(array $params = []) {
    return $this->getClient()
      ->roles()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createRole($namespace, array $params = []) {
    $params['kind'] = 'Role';
    return $this->getClient($namespace)
      ->roles()
      ->create(new K8sRoleModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateRole($namespace, array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    $params['kind'] = 'Role';

    return $this->getClient($namespace)
      ->roles()
      ->update(new K8sRoleModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteRole($namespace, array $params = []) {
    $params['kind'] = 'Role';

    return $this->getClient($namespace)
      ->roles()
      ->delete(new K8sRoleModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getRoleBindings(array $params = []) {
    return $this->getClient()
      ->roleBindings()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createRoleBinding($namespace, array $params = []) {
    $params['kind'] = 'RoleBinding';
    return $this->getClient($namespace)
      ->roleBindings()
      ->create(new K8sRoleBindingModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateRoleBinding($namespace, array $params = []) {
    $params['kind'] = 'RoleBinding';

    return $this->getClient($namespace)
      ->roleBindings()
      ->update(new K8sRoleBindingModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteRoleBinding($namespace, array $params = []) {
    $params['kind'] = 'RoleBinding';

    return $this->getClient($namespace)
      ->roleBindings()
      ->delete(new K8sRoleBindingModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getServiceAccounts(array $params = []) {
    return $this->getClient()
      ->serviceAccounts()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createServiceAccount($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->serviceAccounts()
      ->create(new K8sServiceAccountModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateServiceAccount($namespace, array $params = []) {
    $params['kind'] = 'ServiceAccount';

    return $this->getClient($namespace)
      ->serviceAccounts()
      ->update(new K8sServiceAccountModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteServiceAccount($namespace, array $params = []) {
    $params['kind'] = 'ServiceAccount';

    return $this->getClient($namespace)
      ->serviceAccounts()
      ->delete(new K8sServiceAccountModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getClusterRoles(array $params = []) {
    return $this->getClient()
      ->clusterRoles()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createClusterRole(array $params = []) {
    $params['kind'] = 'ClusterRole';
    return $this->getClient()
      ->clusterRoles()
      ->create(new K8sClusterRoleModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateClusterRole(array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    $params['kind'] = 'ClusterRole';

    return $this->getClient()
      ->clusterRoles()
      ->update(new K8sClusterRoleModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteClusterRole(array $params = []) {
    $params['kind'] = 'ClusterRole';

    return $this->getClient()
      ->clusterRoles()
      ->delete(new K8sClusterRoleModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getClusterRoleBindings(array $params = []) {
    return $this->getClient()
      ->clusterRoleBindings()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createClusterRoleBinding(array $params = []) {
    $params['kind'] = 'ClusterRoleBinding';
    return $this->getClient()
      ->clusterRoleBindings()
      ->create(new K8sClusterRoleBindingModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateClusterRoleBinding(array $params = []) {
    $params['kind'] = 'ClusterRoleBinding';

    return $this->getClient()
      ->clusterRoleBindings()
      ->update(new K8sClusterRoleBindingModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteClusterRoleBinding(array $params = []) {
    $params['kind'] = 'ClusterRoleBinding';

    return $this->getClient()
      ->clusterRoleBindings()
      ->delete(new K8sClusterRoleBindingModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getPersistentVolumes(array $params = []) {
    return $this->getClient()
      ->persistentVolume()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createPersistentVolume(array $params = []) {
    $params['kind'] = 'PersistentVolume';
    return $this->getClient()
      ->persistentVolume()
      ->create(new PersistentVolume($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updatePersistentVolume(array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    $params['kind'] = 'PersistentVolume';

    return $this->getClient()
      ->persistentVolume()
      ->update(new PersistentVolume($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deletePersistentVolume(array $params = []) {
    $params['kind'] = 'PersistentVolume';

    return $this->getClient()
      ->persistentVolume()
      ->delete(new PersistentVolume($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getStorageClasses(array $params = []) {
    return $this->getClient()
      ->storageClasses()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createStorageClass(array $params = []) {
    $params['kind'] = 'StorageClass';
    return $this->getClient()
      ->storageClasses()
      ->create(new K8sStorageClassModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateStorageClass(array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    $params['kind'] = 'StorageClass';

    return $this->getClient()
      ->storageClasses()
      ->update(new K8sStorageClassModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteStorageClass(array $params = []) {
    $params['kind'] = 'StorageClass';

    return $this->getClient()
      ->storageClasses()
      ->delete(new K8sStorageClassModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getStatefulSets(array $params = []) {
    return $this->getClient()
      ->statefulSets()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createStatefulSet($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->statefulSets()
      ->create(new K8sStatefulSetModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateStatefulSet($namespace, array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    $params['kind'] = 'StatefulSet';

    return $this->getClient($namespace)
      ->statefulSets()
      ->update(new K8sStatefulSetModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteStatefulSet($namespace, array $params = []) {
    $params['kind'] = 'StatefulSet';

    return $this->getClient($namespace)
      ->statefulSets()
      ->delete(new K8sStatefulSetModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getIngresses(array $params = []) {
    return $this->getClient()
      ->ingresses()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createIngress($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->ingresses()
      ->create(new Ingress($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateIngress($namespace, array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    $params['kind'] = 'Ingress';

    return $this->getClient($namespace)
      ->ingresses()
      ->update(new Ingress($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteIngress($namespace, array $params = []) {
    $params['kind'] = 'Ingress';

    return $this->getClient($namespace)
      ->ingresses()
      ->delete(new Ingress($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getDaemonSets(array $params = []) {
    return $this->getClient()
      ->daemonSets()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createDaemonSet($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->daemonSets()
      ->create(new DaemonSet($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateDaemonSet($namespace, array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    $params['kind'] = 'DaemonSet';

    return $this->getClient($namespace)
      ->daemonSets()
      ->update(new DaemonSet($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteDaemonSet($namespace, array $params = []) {
    $params['kind'] = 'DaemonSet';

    return $this->getClient($namespace)
      ->daemonSets()
      ->delete(new DaemonSet($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getEndpoints(array $params = []) {
    return $this->getClient()
      ->endpoints()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createEndpoint($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->endpoints()
      ->create(new Endpoint($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateEndpoint($namespace, array $params = []) {
    // Remove empty properties.
    $params['subsets'] = array_filter($params['subsets']);

    $params['kind'] = 'Endpoints';

    return $this->getClient($namespace)
      ->endpoints()
      ->update(new Endpoint($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteEndpoint($namespace, array $params = []) {
    $params['kind'] = 'Endpoints';

    return $this->getClient($namespace)
      ->endpoints()
      ->delete(new Endpoint($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getEvents(array $params = []) {
    return $this->getClient()
      ->events()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function updateEvents(array $params = [], $clear = TRUE) {
    return $this->updateEntities(
      'k8s_event',
      'Event',
      'getEvents',
      'updateEvent',
      $params,
      $clear
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getPersistentVolumeClaims(array $params = []) {
    return $this->getClient()
      ->persistentVolumeClaims()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createPersistentVolumeClaim($namespace, array $params = []) {
    return $this->getClient($namespace)
      ->persistentVolumeClaims()
      ->create(new PersistentVolumeClaim($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updatePersistentVolumeClaim($namespace, array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    $params['kind'] = 'PersistentVolumeClaim';

    return $this->getClient($namespace)
      ->persistentVolumeClaims()
      ->update(new PersistentVolumeClaim($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deletePersistentVolumeClaim($namespace, array $params = []) {
    $params['kind'] = 'PersistentVolumeClaim';

    return $this->getClient($namespace)
      ->persistentVolumeClaims()
      ->delete(new PersistentVolumeClaim($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getApiServices(array $params = []) {
    return $this->getClient()
      ->apiServices()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createApiService(array $params = []) {
    $params['kind'] = 'APIService';
    unset($params['apiVersion']);
    return $this->getClient()
      ->apiServices()
      ->create(new K8sApiServiceModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updateApiService(array $params = []) {
    // Remove empty properties.
    $params['spec'] = array_filter($params['spec']);

    // Remove status, which should not be modified.
    unset($params['status']);

    $params['kind'] = 'ApiService';

    return $this->getClient()
      ->apiServices()
      ->update(new K8sApiServiceModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteApiService(array $params = []) {
    $params['kind'] = 'ApiService';

    return $this->getClient()
      ->apiServices()
      ->delete(new K8sApiServiceModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function getPriorityClasses(array $params = []) {
    return $this->getClient()
      ->priorityClasses()
      ->setFieldSelector($params)
      ->find()
      ->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function createPriorityClass(array $params = []) {
    $params['kind'] = 'PriorityClass';
    return $this->getClient()
      ->priorityClasses()
      ->create(new K8sPriorityClassModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function updatePriorityClass(array $params = []) {
    $params['kind'] = 'PriorityClass';

    return $this->getClient()
      ->priorityClasses()
      ->update(new K8sPriorityClassModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deletePriorityClass(array $params = []) {
    $params['kind'] = 'PriorityClass';

    return $this->getClient()
      ->priorityClasses()
      ->delete(new K8sPriorityClassModel($params));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteResourcesWithEntities(array $entities) {
    foreach ($entities as $entity) {
      $this->setCloudContext($entity->getCloudContext());
      try {
        $short_name = substr($entity->getEntityTypeId(), strlen('k8s_'));
        $name_camel = str_replace(' ', '', ucwords(str_replace('_', ' ', $short_name)));
        $method_name = "delete{$name_camel}";

        if (method_exists($entity, 'getNamespace')) {
          $this->$method_name($entity->getNamespace(), [
            'metadata' => [
              'name' => $entity->getName(),
            ],
          ]);
        }
        else {
          $this->$method_name([
            'metadata' => [
              'name' => $entity->getName(),
            ],
          ]);
        }

        $entity->delete();

        $this->messenger->addStatus(t('The @type @label on @cloud_context has been deleted.', [
          '@type'  => $entity->getEntityType()->getSingularLabel(),
          '@label' => $entity->label(),
          '@cloud_context' => $entity->getCloudContext(),
        ]));

        $this->logOperationMessage($entity, 'deleted');
      }
      catch (K8sServiceException
        | \Exception $e) {

        // Using MessengerTrait::messenger().
        $this->messenger->addError(t('The @type %label on @cloud_context could not be deleted.', [
          '@type'  => $entity->getEntityType()->getSingularLabel(),
          '%label' => $entity->toLink($entity->label())->toString(),
          '@cloud_context' => $entity->getCloudContext(),
        ]));

        $link = [];
        if ($entity->hasLinkTemplate('canonical')) {
          $link = $entity->toLink($this->t('View'))->toString();
        }
        elseif ($entity->hasLinkTemplate('edit-form')) {
          $link = $entity->toLink('View', 'edit-form')->toString();
        }

        $this->logOperationErrorMessage($entity, 'deleted');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function clearAllEntities() {
    $this->clearEntities('k8s_node', time());
    $this->clearEntities('k8s_namespace', time());
    $this->clearEntities('k8s_pod', time());
    $this->clearEntities('k8s_deployment', time());
    $this->clearEntities('k8s_replica_set', time());
    $this->clearEntities('k8s_service', time());
    $this->clearEntities('k8s_service_account', time());
    $this->clearEntities('k8s_cron_job', time());
    $this->clearEntities('k8s_job', time());
    $this->clearEntities('k8s_resource_quota', time());
    $this->clearEntities('k8s_limit_range', time());
    $this->clearEntities('k8s_secret', time());
    $this->clearEntities('k8s_config_map', time());
    $this->clearEntities('k8s_network_policy', time());
    $this->clearEntities('k8s_role', time());
    $this->clearEntities('k8s_cluster_role', time());
    $this->clearEntities('k8s_persistent_volume', time());
    $this->clearEntities('k8s_storage_class', time());
    $this->clearEntities('k8s_stateful_set', time());
    $this->clearEntities('k8s_ingress', time());
    $this->clearEntities('k8s_daemon_set', time());
    $this->clearEntities('k8s_endpoint', time());
    $this->clearEntities('k8s_event', time());
    $this->clearEntities('k8s_persistent_volume_claim', time());
    $this->clearEntities('k8s_cluster_role_binding', time());
    $this->clearEntities('k8s_api_service', time());
    $this->clearEntities('k8s_role_binding', time());
    $this->clearEntities('k8s_priority_class', time());
  }

  /**
   * Setup the default parameters that all API calls will need.
   *
   * @return array
   *   Array of default parameters.
   */
  protected function getDefaultParameters() {
    return [];
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
    $this->logger('k8s_service')->info(
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
   * @param string $extra_key_name
   *   The extra key name.
   * @param bool $batch_mode
   *   Whether updating entities in batch.
   * @param array $extra_data
   *   The extra data.
   *
   * @return bool
   *   True or false depending on lock name.
   *
   * @throws \Drupal\k8s\Service\K8sServiceException
   *   Thrown when unable to get get_entities_method.
   */
  private function updateEntities(
    $entity_type,
    $entity_type_label,
    $get_entities_method,
    $update_entity_method,
    array $params = [],
    $clear = TRUE,
    $extra_key_name = NULL,
    $batch_mode = TRUE,
    array $extra_data = []
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
    catch (K8sServiceException $e) {
      $this->logger('k8s_service')->error($e->getMessage());
    }
    if ($result !== NULL) {
      $all_entities = $this->loadAllEntities($entity_type);
      $stale = [];
      foreach ($all_entities ?: [] as $entity) {
        $key = $entity->getName();
        if (!empty($extra_key_name)) {
          $extra_key_get_method = 'get' . ucfirst($extra_key_name);
          $key .= ':' . $entity->$extra_key_get_method();
        }
        $stale[$key] = $entity;
      }

      if ($batch_mode) {
        /* @var \Drupal\Core\Batch\BatchBuilder $batch_builder */
        $batch_builder = $this->initBatch("$entity_type_label Update");
      }

      foreach ($result ?: [] as $entity) {
        if (is_object($entity)) {
          $entity = $entity->toArray();
        }

        // Keep track of snapshots that do not exist anymore
        // delete them after saving the rest of the snapshots.
        $key = $entity['metadata']['name'] ?? '';
        $metadata_namespace = $entity['metadata']['namespace'] ?? '';

        if (!empty($extra_key_name)) {
          $key .= ":${metadata_namespace}";
        }

        if (isset($stale[$key])) {
          unset($stale[$key]);
        }

        if ($batch_mode) {
          $batch_builder->addOperation([
            K8sBatchOperations::class,
            $update_entity_method,
          ], [$this->cloudContext, $entity, $extra_data]);
        }
        else {
          K8sBatchOperations::$update_entity_method($this->cloudContext, $entity, $extra_data);
        }
      }

      if ($batch_mode) {
        $batch_builder->addOperation([
          K8sBatchOperations::class,
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
   * Get the map of pods metrics.
   *
   * @return array
   *   The map of pods metrics. The key is "Namespace.Name".
   *
   * @throws \Drupal\k8s\Service\K8sServiceException
   *   Thrown when unable to retrieve CPU and Memory usage of pods.
   */
  private function getPodsMetricsMap() {
    $metrics_pods = [];
    try {
      $metrics_pods = $this->getMetricsPods();
    }
    catch (K8sServiceException $e) {
      $this->messenger->addWarning($this->t('Unable to retrieve CPU and Memory usage of pods. Please install <a href="https://github.com/kubernetes-incubator/metrics-server">Kubernetes Metrics Server</a> to K8s.'));
    }

    $metrics = [];
    foreach ($metrics_pods ?: [] as $metrics_pod) {
      if (is_object($metrics_pod)) {
        $metrics_pod = $metrics_pod->toArray();
      }

      $namespace = $metrics_pod['metadata']['namespace'];
      $name = $metrics_pod['metadata']['name'];
      $metrics["$namespace.$name"] = $metrics_pod;
    }

    return $metrics;
  }

  /**
   * Verify the connection of an API server's endpoint.
   *
   * @param string $api_server
   *   The api server's endpoint URL to validate.
   * @param string $token
   *   The token to validate w/ the api server's endpoint.
   *
   * @return bool
   *   Whether the endpoint is accessible or not.
   */
  public function isAccessible($api_server, $token) {
    $accessible = FALSE;
    $response = NULL;
    try {
      // Call API for verifying endpoint.
      $response = \Drupal::httpClient()->get($api_server, [
        'verify' => FALSE,
        'headers' => [
          'Authorization' => "Bearer ${token}",
        ],
      ]);

      // Get Statuscode of Response.
      if (!empty($response) && $response->getStatusCode() === 200) {
        $accessible = TRUE;
      }
    }
    catch (\Exception $e) {
      $accessible = FALSE;
    }

    return $accessible;
  }

  /**
   * Helper method to handle Error cache.
   *
   * @param \Exception $e
   *   The Exception.
   * @param string $cloud_context
   *   The Cloud Context.
   * @param object $entity
   *   The Entity.
   */
  public function handleError(\Exception $e, $cloud_context = '', $entity = NULL): void {

    // Using MessengerTrait::messenger().
    $this->messenger->addError(t('The endpoint is unreachable.'));

    $cloud_service_providers = $this->entityTypeManager
      ->getStorage('cloud_config')
      ->loadByProperties([
        'type' => 'k8s',
        'cloud_context' => $cloud_context,
      ]);

    if (!empty($cloud_service_providers)
    && !empty($cloud_context)) {

      $cloud_service_provider = array_shift($cloud_service_providers);
      $name = $cloud_service_provider->getName();

      $page_link = Link::fromTextAndUrl(
        $name, Url::fromRoute('entity.cloud_config.edit_form', [
          'cloud_config' => $cloud_service_provider->id(),
        ])
      )->toString();

      // Using MessengerTrait::messenger().
      $this->messenger->addError(t('Please check the API server and token: @page_link', [
        '@page_link' => $page_link,
      ]));
    }

    $this->handleException($e);

    // Basically Redirect to Cloud service provider list view page.
    $route_name = 'entity.cloud_config.collection';

    // If an Entity is specified, redirect to the Entity's list view page.
    if ($entity !== NULL
    && !empty($cloud_context)) {
      $route_name = "entity.{$entity->getEntityTypeId()}.collection";
    }

    $redirect_url = Url::fromRoute($route_name, [
      'cloud_context' => $cloud_context,
    ]);

    $redirect_response = new RedirectResponse($redirect_url->toString());
    $redirect_response->send();
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
   * Get link for the metrics server.
   *
   * @param string $cloud_context
   *   The cloud context.
   *
   * @return string
   *   The link for the metrics server.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getMetricsServerLink($cloud_context = NULL) {
    $metrics_server_url = Url::fromUri('https://github.com/kubernetes-incubator/metrics-server');
    if (!empty($cloud_context)) {
      $templates = $this->entityTypeManager
        ->getStorage('cloud_server_template')
        ->loadByProperties(
          [
            'cloud_context' => $cloud_context,
            'name' => 'metrics_server',
          ]
        );

      if (!empty($templates)) {
        $template = reset($templates);
        $metrics_server_url = Url::fromRoute(
          'entity.cloud_server_template.launch',
          [
            'cloud_context' => $cloud_context,
            'cloud_server_template' => $template->id(),
          ]
        );
      }
    }

    return Link::fromTextAndUrl(
      t('Kubernetes Metrics Server'),
      $metrics_server_url
    )->toString();
  }

  /**
   * Run the time scheduler. Change resource quota params based on the schedule.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function runTimeScheduler(): void {
    $entities = \Drupal::entityTypeManager()->getStorage('cloud_project')
      ->loadByProperties([
        'field_enable_time_scheduler' => 1,
      ]);

    foreach ($entities ?: [] as $entity) {
      $namespace = $entity->getName();
      $namespaces = $this->entityTypeManager->getStorage('k8s_namespace')
        ->loadByProperties([
          'name' => $namespace,
          'status' => 'Active',
        ]);

      if (empty($namespaces)) {
        continue;
      }

      $k8s_cluster_list = [];
      $k8s_clusters = $entity->get('field_k8s_clusters');
      foreach ($k8s_clusters ?: [] as $cloud_context) {
        if (!empty($cloud_context->value)) {
          $k8s_cluster_list[] = $cloud_context->value;
        }
      }

      // Use the changed time with current time.
      $resources = $this->entityTypeManager->getStorage('k8s_resource_quota')
        ->loadByProperties([
          'name' => $namespace,
        ]);

      $last_change_time = NULL;
      $resource = NULL;
      if (!empty($resources)) {
        $resource = array_shift($resources);
        $last_change_time = $resource->changed();
      }

      $startup_time = "{$entity->get('field_startup_time_hour')->value}:{$entity->get('field_startup_time_minute')->value}";
      $stop_time = "{$entity->get('field_stop_time_hour')->value}:{$entity->get('field_stop_time_minute')->value}";

      $is_current_time = $this->validateScheduledTime($startup_time, $stop_time);
      $is_previous_time = $this->validateScheduledTime($startup_time, $stop_time, $last_change_time);
      // Handle a case when all clusters are NOT available.
      if (!$is_current_time) {
        // Initialize the params (zero clear) for resource quota in case of
        // 'create' and 'update' in order to invalidate the resource quota.
        $params_resource_quota = [
          'metadata' => ['name' => $namespace],
          'spec' => [
            'hard' => [
              'cpu' => '0m',
              'memory' => '0Mi',
              'pods' => 0,
            ],
          ],
        ];
        if (empty($last_change_time) || $is_previous_time) {
          $this->changeResourceQuota($namespace, $k8s_cluster_list, $params_resource_quota);
          continue;
        }
      }

      // Handle a case when all clusters are available.
      $enable_resource_scheduler = $entity->get('field_enable_resource_scheduler')->value;
      // If the 'Enable resource schedule' option is NOT checked, simply
      // delete the resource quota objects from each cluster.
      if (empty($enable_resource_scheduler)) {
        $resources = $this->entityTypeManager->getStorage('k8s_resource_quota')
          ->loadByProperties([
            'name' => $namespace,
          ]);
        $this->deleteResourcesWithEntities($resources);
        continue;
      }

      // If the 'Enable resource schedule' option is checked, set params for
      // resource quota.
      if (!$is_previous_time) {
        $params_resource_quota = [
          'metadata' => ['name' => $namespace],
          'spec' => [
            'hard' => [
              'cpu' => Html::escape($entity->get('field_request_cpu')->value) . 'm',
              'memory' => Html::escape($entity->get('field_request_memory')->value) . 'Mi',
              'pods' => Html::escape($entity->get('field_pod_count')->value),
            ],
          ],
        ];
        $this->changeResourceQuota($namespace, $k8s_cluster_list, $params_resource_quota);
      }
    }
  }

  /**
   * Not allocate resource to set namespace.
   *
   * @param string $label
   *   The name of namespace in Kubernetes cluster.
   * @param array $k8s_cluster_list
   *   The list of cloud context.
   * @param array $param
   *   Parameter set on resource quota.
   */
  public function changeResourceQuota($label, array $k8s_cluster_list, array $param): void {
    foreach ($k8s_cluster_list ?: [] as $cloud_context) {
      $this->setCloudContext($cloud_context);
      $this->updateResourceWithEntity('k8s_resource_quota', $cloud_context, $label, $param);
      $message_all = $this->messenger->all();
      $messages = array_shift($message_all);
      $output = '';
      foreach ($messages ?: [] as $message) {
        $output .= $message;
      }
      $this->logger('k8s')->info($output);
    }
  }

  /**
   * Determine whether the current time is within specific time range or not.
   *
   * @param string $startup_time
   *   The start of specific time.
   * @param string $stop_time
   *   The end of specific time.
   * @param int $time
   *   The time to be validated with unix epoch time.
   *
   * @return bool
   *   TRUE means that input time is in specific time range
   *   defined by startup and stop time.
   */
  public function validateScheduledTime($startup_time, $stop_time, $time = NULL): bool {
    if (empty($time)) {
      $time = time();
    }
    $start_time = strtotime($startup_time);
    $end_time = strtotime($stop_time);
    $diff_current_time = $time - $start_time;
    $diff_end_time = $end_time - $start_time;
    if ($diff_end_time < 0) {
      $diff_current_time > $diff_end_time
        ? $end_time = strtotime('+1 day', $end_time)
        : $start_time = strtotime('-1 day', $start_time);
    }
    return $start_time <= $time && $time <= $end_time;
  }

  /**
   * Create/update resource with entity.
   *
   * @param string $type
   *   Entity type.
   * @param string $cloud_context
   *   Cloud context.
   * @param string $label
   *   The label.
   * @param array $params
   *   Parameter for type.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateResourceWithEntity($type, $cloud_context, $label, array $params): void {

    $passive_operation = [
      'create' => 'created',
      'update' => 'updated',
    ];

    $entity_type = $this->entityTypeManager->getStorage($type);
    $entities = $entity_type->loadByProperties([
      'name' => $label,
      'cloud_context' => $cloud_context,
    ]);

    $action = empty($entities)
      ? 'create'
      : 'update';

    $dummy = $entity_type->create([]);
    $name_camel = $this->getShortEntityTypeNameCamel($dummy);
    $name_plural_camel = $this->getShortEntityTypeNamePluralCamel($dummy);
    $dummy->delete();

    try {
      if (empty($entities)) {
        $method = $action . $name_camel;
        $type === 'k8s_namespace'
          ? $this->$method($params)
          : $this->$method($label, $params);
        $method = 'update' . $name_plural_camel;
        $this->$method([
          'metadata.name' => $label,
        ], FALSE);
      }
      else {
        $entity = array_shift($entities);
        $method = $action . $name_camel;
        $type === 'k8s_namespace'
          ? $this->$method($params)
          : $this->$method($label, $params);
        $entity->save();
      }

      $entity_id = $this->getEntityId($type, 'name', $label);

      $this->messenger->addStatus($this->t('The @type %label on @cloud_context has been @passive_operation.', [
        '@type' => $entity_type->getEntityType()->getSingularLabel(),
        '%label' => $entity_type->load($entity_id)->toLink()->toString(),
        '@cloud_context' => $cloud_context,
        '@passive_operation' => $passive_operation[$action],
      ]));
    }
    catch (\Exception $e) {

      $this->messenger->addError($this->t('The @type %label on @cloud_context could not be @passive_operation.', [
        '@type' => $entity_type->getEntityType()->getSingularLabel(),
        '%label' => $label,
        '@cloud_context' => $cloud_context,
        '@passive_operation' => $passive_operation[$action],
      ]));
    }
  }

  /**
   * Export node metrics to log.
   *
   * @param array $metrics_nodes
   *   The metrics of nodes.
   */
  public function exportNodeMetrics(array $metrics_nodes) {
    $metrics = [];
    foreach ($metrics_nodes ?: [] as $metrics_node) {
      if (is_object($metrics_node)) {
        $metrics_node = $metrics_node->toArray();
      }
      $node_name = $metrics_node['metadata']['name'];
      $cpu = k8s_convert_cpu_to_float($metrics_node['usage']['cpu']);
      $memory = k8s_convert_memory_to_integer($metrics_node['usage']['memory']);
      $metrics[$this->cloudContext]['nodes'][$node_name] = [
        'cpu' => $cpu,
        'memory' => $memory,
      ];
    }

    $this->logger('k8s_metrics')->info((Yaml::encode($metrics)));
  }

  /**
   * Export pod metrics to log.
   *
   * @param array $metrics_pods
   *   The metrics of nodes.
   */
  public function exportPodMetrics(array $metrics_pods) {
    $metrics = [];
    foreach ($metrics_pods ?: [] as $metrics_pod) {
      if (is_object($metrics_pod)) {
        $metrics_pod = $metrics_pod->toArray();
      }
      $pod_namespace = $metrics_pod['metadata']['namespace'];
      $pod_name = $metrics_pod['metadata']['name'];

      $cpu = 0;
      $memory = 0;
      foreach ($metrics_pod['containers'] ?: [] as $container) {
        $cpu += k8s_convert_cpu_to_float($container['usage']['cpu']);
        $memory += k8s_convert_memory_to_integer($container['usage']['memory']);

      }

      $metrics[$this->cloudContext]['pods']["$pod_namespace:$pod_name"] = [
        'cpu' => $cpu,
        'memory' => $memory,
      ];
    }

    $this->logger('k8s_metrics')->info((Yaml::encode($metrics)));
  }

  /**
   * Create queue items for update resources queue.
   */
  public function createResourceQueueItems() {
    $update_resources_queue = $this->queueFactory->get('k8s_update_resources_queue');
    $method_names = [
      'updateNodes',
      'updateNamespaces',
      'updatePods',
      'updateDeployments',
      'updateReplicaSets',
      'updateServices',
      'updateCronJobs',
      'updateJobs',
      'updateResourceQuotas',
      'updateLimitRanges',
      'updateSecrets',
      'updateConfigMaps',
      'updateNetworkPolicies',
      'updateRoles',
      'updateClusterRoles',
      'updatePersistentVolumes',
      'updateStorageClasses',
      'updateStatefulSets',
      'updateIngresses',
      'updateDaemonSets',
      'updateEndpoints',
      'updateEvents',
      'updatePersistentVolumeClaims',
      'updateClusterRoleBindings',
      'updateApiServices',
      'updateRoleBindings',
      'updateServiceAccounts',
    ];
    foreach ($method_names as $method_name) {
      $update_resources_queue->createItem([
        'cloud_context' => $this->cloudContext,
        'k8s_method_name' => $method_name,
      ]);
    }
  }

  /**
   * Create queue items for update cost storage queue.
   */
  public function createCostStorageQueueItems() {
    $plugin = \Drupal::service('plugin.manager.cloud_cost_storage');
    foreach ($plugin->getDefinitions() ?: [] as $id => $definition) {
      if ($id === 'K8s_cloud_cost_storage') {
        $plugin_id = $plugin->createInstance($id);
        $plugin_id->updateResourceStorageEntity();
        $plugin_id->updateCostStorageEntity();
        $plugin_id->deleteResourceStorageEntity();
      }
    }
  }

  /**
   * Format costs.
   *
   * @param int $costs
   *   The costs.
   * @param int $total_costs
   *   The total costs.
   *
   * @return string
   *   The formatted costs string.
   */
  public function formatCosts($costs, $total_costs) {
    $costs_str = round($costs, 2);
    $percentage = round($costs / $total_costs * 100, 2);
    return "$costs_str ($percentage%)";
  }

  /**
   * Format cpu usage.
   *
   * @param float $cpu_usage
   *   The cpu usage.
   * @param float $cpu_capacity
   *   The cpu capacity.
   *
   * @return string
   *   The formatted cpu usage string.
   */
  public function formatCpuUsage($cpu_usage, $cpu_capacity) {
    $cpu_str = round($cpu_usage, 2);
    $percentage = round($cpu_usage / $cpu_capacity * 100, 2);
    return "$cpu_str ($percentage%)";
  }

  /**
   * Format memory usage.
   *
   * @param int $memory_usage
   *   The memory usage.
   * @param int $memory_capacity
   *   The memory capacity.
   *
   * @return string
   *   The formatted memory usage string.
   */
  public function formatMemoryUsage($memory_usage, $memory_capacity) {
    $memory_str = k8s_format_memory($memory_usage);
    $percentage = round($memory_usage / $memory_capacity * 100, 2);
    return "$memory_str ($percentage%)";
  }

  /**
   * Format pod usage.
   *
   * @param int $pod_usage
   *   The pod usage.
   * @param int $pod_capacity
   *   The pod capacity.
   *
   * @return string
   *   The formatted pod usage string.
   */
  public function formatPodUsage($pod_usage, $pod_capacity): string {
    $percentage = round($pod_usage / $pod_capacity * 100, 2);
    return "$pod_usage/$pod_capacity ($percentage%)";
  }

  /**
   * Get total costs of nodes.
   *
   * @param array $nodes
   *   The k8s_node entities.
   *
   * @return int
   *   The total costs of nodes.
   */
  public function getTotalCosts(array $nodes) {
    $costs = 0;

    if (!$this->moduleHandler->moduleExists('aws_cloud')) {
      return $costs;
    }

    /* @var \Drupal\aws_cloud\Service\Pricing\InstanceTypePriceDataProvider $price_data_provider */
    $price_date_provider = \Drupal::service('aws_cloud.instance_type_price_data_provider');
    if (empty($price_date_provider)) {
      return $costs;
    }

    // @TODO: User can accordingly change the parameters in future.
    $cost_type = 'on_demand_yearly';

    foreach ($nodes ?: [] as $node) {
      // Get instance type and region.
      $region = NULL;
      $instance_type = NULL;
      $labels = $node->get('labels');
      foreach ($labels ?: [] as $item) {
        if ($item->getItemKey() === 'beta.kubernetes.io/instance-type') {
          $instance_type = $item->getItemValue();
        }
        elseif ($item->getItemKey() === 'failure-domain.beta.kubernetes.io/region') {
          $region = $item->getItemValue();
        }
      }

      if (empty($instance_type) || empty($region)) {
        continue;
      }

      $price_data = $price_date_provider->getDataByRegion($region);
      foreach ($price_data ?: [] as $item) {
        if ($item['instance_type'] === $instance_type) {
          if (!empty($item[$cost_type])) {
            if ($cost_type === 'ri_three_year') {
              $costs += $item[$cost_type] / 3;
            }
            else {
              $costs += $item[$cost_type];
            }
          }
          break;
        }
      }
    }

    return $costs;
  }

  /**
   * Get amount of resource usage per a namespace.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param string $namespace
   *   The namespace of kubernetes.
   *
   * @return array
   *   The amount of resource usage.
   */
  public function getNamespaceResourceUsage($cloud_context, $namespace): array {
    $row = [
      'cpu_usage' => 0,
      'memory_usage' => 0,
      'pod_usage' => 0,
      'collect_time' => time(),
    ];
    $pods = $this->entityTypeManager
      ->getStorage('k8s_pod')->loadByProperties([
        'cloud_context' => $cloud_context,
        'namespace' => $namespace,
      ]);
    if (empty($pods)) {
      return $row;
    }

    $row['namespace'] = [
      'data' => [
        '#type' => 'link',
        '#title' => $namespace,
        '#url' => Url::fromRoute(
          'entity.k8s_namespace.canonical',
          [
            'cloud_context' => $cloud_context,
            'k8s_namespace' => $namespace,
          ]
        ),
      ],
    ];

    $cpu_usage = array_sum(array_map(static function ($pod) {
      return $pod->getCpuUsage();
    }, $pods));

    $memory_usage = array_sum(array_map(static function ($pod) {
      return $pod->getMemoryUsage();
    }, $pods));
    $pod_usage = count($pods);

    $row['cpu_usage'] = $cpu_usage;
    $row['memory_usage'] = $memory_usage;
    $row['pod_usage'] = $pod_usage;

    return $row;
  }

  /**
   * Get amount of resource usage per a namespace.
   *
   * @param string $cloud_context
   *   The cloud context.
   *
   * @return array
   *   Some resource usage.
   */
  public function getNodeResourceUsage($cloud_context): array {
    $row = [
      'cpu_capacity' => 0,
      'memory_capacity' => 0,
      'pod_capacity' => 0,
      'total_costs' => 0,
    ];
    $nodes = $this->entityTypeManager
      ->getStorage('k8s_node')->loadByProperties(
        [
          'cloud_context' => $cloud_context,
        ]
      );
    if (empty($nodes)) {
      return $row;
    }

    $total_cost = $this->getTotalCosts($nodes);
    $cpu_capacity = array_sum(array_map(static function ($node) {
      return $node->getCpuCapacity();
    }, $nodes));

    $memory_capacity = array_sum(array_map(static function ($node) {
      return $node->getMemoryCapacity();
    }, $nodes));

    $pod_capacity = array_sum(array_map(static function ($node) {
      return $node->getPodsCapacity();
    }, $nodes));

    $row['cpu_capacity'] = $cpu_capacity;
    $row['memory_capacity'] = $memory_capacity;
    $row['pod_capacity'] = $pod_capacity;
    $row['total_costs'] = $total_cost;
    return $row;
  }

  /**
   * Get amount of resource usage per a namespace.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param string $namespace
   *   The label.
   *
   * @return array
   *   Cost and the related things.
   */
  public function calculateCostPerNamespace($cloud_context, $namespace): array {
    // Get the metrics about resource usage.
    $namespaces_resource = $this->getNamespaceResourceUsage($cloud_context, $namespace);
    $nodes_resource = $this->getNodeResourceUsage($cloud_context);
    $cpu_usage = $namespaces_resource['cpu_usage'];
    $memory_usage = $namespaces_resource['memory_usage'];
    $pod_usage = $namespaces_resource['pod_usage'];
    $cpu_capacity = $nodes_resource['cpu_capacity'];
    $memory_capacity = $nodes_resource['memory_capacity'];
    $pod_capacity = $nodes_resource['pod_capacity'];
    $total_costs = $nodes_resource['total_costs'];
    $collect_time = $namespaces_resource['collect_time'];

    // Calculate cost.
    $cost = $total_costs > 0 ? ($cpu_usage / $cpu_capacity + $memory_usage / $memory_capacity + $pod_usage / $pod_capacity) / 3 * $total_costs : 0;

    return [
      'resources' => [
        'cpu_usage' => $cpu_usage,
        'memory_usage' => $memory_usage,
        'pod_usage' => $pod_usage,
        'cpu_capacity' => $cpu_capacity,
        'memory_capacity' => $memory_capacity,
        'pod_capacity' => $pod_capacity,
        'instance_cost' => $total_costs,
      ],
      'cost' => $cost,
      'collect_time' => $collect_time,
    ];
  }

}
