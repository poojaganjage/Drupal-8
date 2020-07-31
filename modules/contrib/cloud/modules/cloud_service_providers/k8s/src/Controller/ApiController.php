<?php

namespace Drupal\k8s\Controller;

use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Render\RendererInterface;
use Drupal\cloud\Entity\CloudProjectInterface;
use Drupal\k8s\Entity\K8sNode;
use Drupal\k8s\Entity\K8sPod;
use Drupal\k8s\Service\K8sService;
use Drupal\k8s\Service\K8sServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller responsible for "update" urls.
 *
 * This class is mainly responsible for
 * updating the K8s entities from urls.
 */
class ApiController extends ControllerBase implements ApiControllerInterface {

  use CloudContentEntityTrait;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The K8s Service.
   *
   * @var \Drupal\k8s\Service\K8sServiceInterface
   */
  private $k8sService;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  private $renderer;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * ApiController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\k8s\Service\K8sServiceInterface $k8s_service
   *   Object for interfacing with K8s API.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   Messanger Object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack object.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Database\Connection $database
   *   The database service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    K8sServiceInterface $k8s_service,
    Messenger $messenger,
    RequestStack $request_stack,
    RendererInterface $renderer,
    Connection $database
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->k8sService = $k8s_service;
    $this->messenger = $messenger;
    $this->requestStack = $request_stack;
    $this->renderer = $renderer;
    $this->database = $database;
  }

  /**
   * Dependency Injection.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Instance of ContainerInterface.
   *
   * @return ApiController
   *   return created object.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('k8s'),
      $container->get('messenger'),
      $container->get('request_stack'),
      $container->get('renderer'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateAllResources($cloud_context) {
    k8s_update_resources($cloud_context);

    return $this->redirect('view.k8s_node.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function updateNodeList($cloud_context) {
    return $this->updateEntityList('node', 'nodes', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateNamespaceList($cloud_context) {
    return $this->updateEntityList('namespace', 'namespaces', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updatePodList($cloud_context) {
    return $this->updateEntityList('pod', 'pods', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateNetworkPolicyList($cloud_context) {
    return $this->updateEntityList('network_policy', 'network_policies', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateDeploymentList($cloud_context) {
    return $this->updateEntityList('deployment', 'deployments', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateReplicaSetList($cloud_context) {
    return $this->updateEntityList('replica_set', 'replica_sets', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateServiceList($cloud_context) {
    return $this->updateEntityList('service', 'services', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateCronJobList($cloud_context) {
    return $this->updateEntityList('cron_job', 'cron_jobs', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateJobList($cloud_context) {
    return $this->updateEntityList('job', 'jobs', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateResourceQuotaList($cloud_context) {
    return $this->updateEntityList('resource_quota', 'resource_quotas', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateLimitRangeList($cloud_context) {
    return $this->updateEntityList('limit_range', 'limit_ranges', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateSecretList($cloud_context) {
    return $this->updateEntityList('secret', 'secrets', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateConfigMapList($cloud_context) {
    return $this->updateEntityList('config_map', 'config_maps', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateRoleList($cloud_context) {
    return $this->updateEntityList('role', 'roles', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateClusterRoleList($cloud_context) {
    return $this->updateEntityList('cluster_role', 'cluster_roles', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updatePersistentVolumeList($cloud_context) {
    return $this->updateEntityList('persistent_volume', 'persistent_volumes', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateStorageClassList($cloud_context) {
    return $this->updateEntityList('storage_class', 'storage_classes', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateStatefulSetsList($cloud_context) {
    return $this->updateEntityList('stateful_set', 'stateful_sets', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateIngressList($cloud_context) {
    return $this->updateEntityList('ingress', 'ingresses', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateDaemonSetList($cloud_context) {
    return $this->updateEntityList('daemon_set', 'daemon_sets', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateEndpointList($cloud_context) {
    return $this->updateEntityList('endpoint', 'endpoints', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateEventList($cloud_context) {
    return $this->updateEntityList('event', 'events', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updatePersistentVolumeClaimList($cloud_context) {
    return $this->updateEntityList('persistent_volume_claim', 'persistent_volume_claims', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateClusterRoleBindingList($cloud_context) {
    return $this->updateEntityList('cluster_role_binding', 'cluster_role_bindings', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateServiceAccountsList($cloud_context) {
    return $this->updateEntityList('service_account', 'service_accounts', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateApiServiceList($cloud_context) {
    return $this->updateEntityList('api_service', 'api_services', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateRoleBindingsList($cloud_context) {
    return $this->updateEntityList('role_binding', 'role_bindings', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updatePriorityClassesList($cloud_context) {
    return $this->updateEntityList('priority_class', 'priority_classes', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function getNodeMetrics($cloud_context, K8sNode $k8s_node) {
    $node_name = $k8s_node->getName();

    $result = $this->database
      ->select('watchdog', 'w')
      ->fields('w', ['message', 'timestamp'])
      ->condition('type', 'k8s_metrics')
      ->condition('timestamp', time() - 7 * 24 * 60 * 60, '>=')
      ->condition('message', "%$node_name%", 'LIKE')
      ->orderBy('timestamp')
      ->execute();

    $data = [];
    foreach ($result ?: [] as $record) {
      $message = Yaml::decode($record->message);
      if (empty($message[$cloud_context])) {
        continue;
      }

      $cpu = NULL;
      if (empty($message[$cloud_context]['nodes'][$node_name])) {
        continue;
      }

      $cpu = $message[$cloud_context]['nodes'][$node_name]['cpu'];
      $memory = $message[$cloud_context]['nodes'][$node_name]['memory'] ?? 0;
      $data[] = [
        'timestamp' => $record->timestamp,
        'cpu' => $cpu,
        'memory' => $memory,
      ];
    }

    return new JsonResponse($data);
  }

  /**
   * {@inheritdoc}
   */
  public function getPodMetrics($cloud_context, K8sPod $k8s_pod) {
    $pod_name = $k8s_pod->getName();
    $pod_namespace = $k8s_pod->getNamespace();

    $result = $this->database
      ->select('watchdog', 'w')
      ->fields('w', ['message', 'timestamp'])
      ->condition('type', 'k8s_metrics')
      ->condition('timestamp', time() - 7 * 24 * 60 * 60, '>=')
      ->condition('message', "%$pod_namespace:$pod_name%", 'LIKE')
      ->orderBy('timestamp')
      ->execute();

    $data = [];
    foreach ($result ?: [] as $record) {
      $message = Yaml::decode($record->message);
      if (empty($message[$cloud_context])) {
        continue;
      }

      $cpu = NULL;
      $key = "$pod_namespace:$pod_name";
      if (empty($message[$cloud_context]['pods'][$key])) {
        continue;
      }

      $cpu = $message[$cloud_context]['pods'][$key]['cpu'];
      $memory = $message[$cloud_context]['pods'][$key]['memory'] ?? 0;
      $data[] = [
        'timestamp' => $record->timestamp,
        'cpu' => $cpu,
        'memory' => $memory,
      ];
    }

    return new JsonResponse($data);
  }

  /**
   * Get all metrics information on all nodes in a cluster of $cloud_context.
   *
   * @param string $cloud_context
   *   The cloud context.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   All metrics information on all nodes across a cluster specified by
   *   $cloud_context.
   */
  public function getNodeAllocatedResourcesList($cloud_context) {
    return $this->getNodeAllocatedResources($cloud_context);
  }

  /**
   * Get the metrics information on all node(s)
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The metrics information on node(s).
   */
  public function getAllNodeAllocatedResources() : JsonResponse {
    $response = [];
    try {
      $ids = $this->entityTypeManager->getStorage('k8s_node')
        ->getQuery()
        ->execute();

      $nodes = $this->entityTypeManager->getStorage('k8s_node')
        ->loadMultiple($ids);

      if (!empty($nodes)) {
        $response = $this->buildAllocatedResourceArray($nodes);
      }
    }
    catch (\Exception $e) {
      $this->handleException($e);
      $response = [];
    }
    return new JsonResponse($response);
  }

  /**
   * Get the metrics information on the node(s)
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param \Drupal\cloud\Entity\CloudProjectInterface $cloud_project
   *   The cloud project.
   * @param \Drupal\k8s\Entity\K8sNode $k8s_node
   *   The K8s node entity to expect to get the metrics information.  If this
   *   parameter is omitted, it assumes that all nodes are specified.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The metrics information on node(s).
   */
  public function getNodeAllocatedResources($cloud_context, CloudProjectInterface $cloud_project = NULL, K8sNode $k8s_node = NULL) : JsonResponse {
    $response = [];
    $nodes = [];

    try {
      if (!empty($cloud_project)) {
        $k8s_clusters = $cloud_project->get('field_k8s_clusters')->getValue();
        $k8_nodes = [];
        foreach ($k8s_clusters ?: [] as $k8s_cluster) {
          $k8_nodes[] = $this->entityTypeManager()
            ->getStorage('k8s_node')
            ->loadByProperties([
              'cloud_context' => $k8s_cluster['value'],
            ]);
        }
        // Merge the arrays outside the for loop.
        // Merging an array inside a for loop is memory intensive.
        // https://github.com/dseguy/clearPHP/blob/master/rules/no-array_merge-in-loop.md
        $nodes = call_user_func_array('array_merge', $k8_nodes);
      }
      else {
        $nodes = isset($k8s_node)
          ? $this->entityTypeManager->getStorage('k8s_node')
            ->loadByProperties([
              'cloud_context' => $cloud_context,
              'id' => $k8s_node->id(),
            ])
          : $this->entityTypeManager->getStorage('k8s_node')
            ->loadByProperties([
              'cloud_context' => $cloud_context,
            ]);
      }

      if (!empty($nodes)) {
        $response = $this->buildAllocatedResourceArray($nodes, $cloud_context);
      }
    }
    catch (\Exception $e) {
      $this->handleException($e);
      $response = [];
    }
    return new JsonResponse($response);
  }

  /**
   * Helper method to build the allocated resource array.
   *
   * @param array $nodes
   *   Array of K8 nodes.
   * @param string $cloud_context
   *   Optional cloud context.
   *
   * @return array
   *   Array of nodes.
   */
  private function buildAllocatedResourceArray(array $nodes, $cloud_context = NULL) : array {
    $response = [];
    foreach ($nodes ?: [] as $node) {
      $node_name = $node->getName();
      $pod_resources = $this->getPodResources($node->getName(), $cloud_context);

      // Return a JSON object of K8s Node names, Pods capacity and Pods
      // allocation to construct a Node heatmap to display the Pods allocation
      // status by D3.js.
      $node_response = $this->generateNodeResponse($node);
      $node_response['pods'] = $pod_resources;
      $response[] = $node_response;
    }
    return $response;
  }

  /**
   * Helper method to update entities.
   *
   * @param string $entity_type_name
   *   The entity type name.
   * @param string $entity_type_name_plural
   *   The plural format of entity type name.
   * @param string $cloud_context
   *   The cloud context.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   An associative array with a redirect route and any parameters to build
   *   the route.
   */
  private function updateEntityList($entity_type_name, $entity_type_name_plural, $cloud_context) {
    $entity_type_name_capital = str_replace('_', '', ucwords($entity_type_name, '_'));
    $entity_type_name_capital_plural = str_replace('_', '', ucwords($entity_type_name_plural, '_'));

    $this->k8sService->setCloudContext($cloud_context);
    $update_method_name = 'update' . $entity_type_name_capital_plural;
    $updated = $this->k8sService->$update_method_name();

    if ($updated !== FALSE) {
      $this->messageUser($this->t('Updated @name.', ['@name' => $entity_type_name_capital_plural]));
      K8sService::clearCacheValue();
    }
    else {
      $this->messageUser($this->t('Unable to update @name.', ['@name' => $entity_type_name_capital_plural]), 'error');
    }

    // Update the cache.
    K8sService::clearCacheValue();

    return $this->redirect("view.k8s_$entity_type_name.list", [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * Helper method to add messages for the end user.
   *
   * @param string $message
   *   The message.
   * @param string $type
   *   The message type: error or message.
   */
  private function messageUser($message, $type = 'message') {
    switch ($type) {
      case 'error':
        $this->messenger->addError($message);
        break;

      case 'message':
        $this->messenger->addStatus($message);
      default:
        break;
    }
  }

  /**
   * Return JSON object of K8s Node names, Pods capacity and Pods allocation.
   *
   * Used to construct a Node heat map to display the Pods allocation
   * status by D3.js.
   *
   * @param \Drupal\k8s\Entity\K8sNode $node
   *   The K8sNode to generate for.
   *
   * @return array
   *   Formatted array of information.
   */
  private function generateNodeResponse(K8sNode $node) {
    // Return a JSON object of K8s Node names, Pods capacity and Pods
    // allocation to construct a Node heatmap to display the Pods allocation
    // status by D3.js.
    return [
      // Node Name.
      'name' => $node->getName() ?: 'Unknown Node Name',

      // CPU usage info.
      'cpuCapacity' => $node->getCpuCapacity(),
      'cpuRequest' => $node->getCpuRequest(),
      'cpuLimit' => $node->getCpuLimit(),

      // Memory usage info.
      'memoryCapacity' => $node->getMemoryCapacity(),
      'memoryRequest' => $node->getMemoryRequest(),
      'memoryLimit' => $node->getMemoryLimit(),

      // Pods usage info.
      'podsCapacity' => $node->getPodsCapacity(),
      'podsAllocation' => $node->getPodsAllocation(),
    ];
  }

  /**
   * Load pod resources based on node name and/or cloud_context.
   *
   * @param string $node_name
   *   The node name to load.
   * @param string $cloud_context
   *   The cloud_context if passed.
   *
   * @return array
   *   Pod resource array.
   */
  private function getPodResources($node_name, $cloud_context = NULL) {
    $pod_resources = [];
    try {
      $params = [
        'node_name' => $node_name,
      ];
      if (!empty($cloud_context)) {
        $params['cloud_context'] = $cloud_context;
      }
      $pods = $this->entityTypeManager->getStorage('k8s_pod')
        ->loadByProperties($params);

      foreach ($pods ?: [] as $pod) {
        $pod_resources[] = [
          'name' => $pod->getName() ?: 'Unknown Pod Name',
          'cpuUsage' => $pod->getCpuUsage(),
          'memoryUsage' => $pod->getMemoryUsage(),
        ];
      }
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }
    return $pod_resources;
  }

}
