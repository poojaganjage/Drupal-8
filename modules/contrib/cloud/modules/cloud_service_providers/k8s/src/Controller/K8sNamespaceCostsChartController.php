<?php

namespace Drupal\k8s\Controller;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Url;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\cloud\Entity\CloudProjectInterface;
use Drupal\k8s\Entity\K8sNamespaceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Controller responsible for K8s Namespace Costs Chart.
 */
class K8sNamespaceCostsChartController extends ControllerBase {

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * CloudConfigLocationController constructor.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   Route Provider.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   * @param \Drupal\Core\Database\Connection $database
   *   The database service.
   */
  public function __construct(RouteProviderInterface $route_provider, Request $request, Connection $database) {
    $this->routeProvider = $route_provider;
    $this->request = $request;
    $this->database = $database;
  }

  /**
   * Dependency Injection.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Instance of ContainerInterface.
   *
   * @return K8sNamespaceCostsChartController
   *   return created object.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('router.route_provider'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('database')
    );
  }

  /**
   * Checks user access for k8s namespace costs chart.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   * @param \Symfony\Component\Routing\Route $route
   *   The route object.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function access(AccountInterface $account, Route $route) {
    global $base_url;
    // Get the referer url.
    $referer = $this->request->headers->get('referer');
    if (!empty($referer)) {
      // Get the alias or the referer.
      $alias = substr($referer, strlen($base_url));
      $url = Url::fromUri("internal:$alias");
      if ($url->access($account)) {
        return AccessResult::allowed();
      }
    }
    return AccessResult::forbidden();
  }

  /**
   * Get cost for all namespaces.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response of a K8s Namespace Costs.
   */
  public function getAllK8sNamespaceCosts() {
    $node_ids = $this->entityTypeManager()
      ->getStorage('k8s_node')
      ->getQuery()
      ->execute();
    $nodes = $this->entityTypeManager()
      ->getStorage('k8s_node')->loadMultiple($node_ids);

    $namespace_ids = $this->entityTypeManager()
      ->getStorage('k8s_namespace')
      ->getQuery()
      ->execute();

    $namespaces = $this->entityTypeManager()
      ->getStorage('k8s_namespace')->loadMultiple($namespace_ids);

    return $this->calculateNamespaceCosts($namespaces, $nodes);
  }

  /**
   * Get K8s Namespace Costs.
   *
   * @param string $cloud_context
   *   Cloud context string.
   * @param \Drupal\cloud\Entity\CloudProjectInterface $cloud_project
   *   The cloud project.
   * @param \Drupal\k8s\Entity\K8sNamespaceInterface $k8s_namespace
   *   K8s Namespace entity.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response of a K8s Namespace Costs.
   */
  public function getK8sNamespaceCosts($cloud_context, CloudProjectInterface $cloud_project = NULL, K8sNamespaceInterface $k8s_namespace = NULL) {
    $nodes = [];
    $namespaces = [];
    $cloud_contexts = [];

    if (!empty($cloud_project)) {
      $k8s_clusters = $cloud_project->get('field_k8s_clusters')->getValue();
      foreach ($k8s_clusters ?: [] as $k8s_cluster) {
        $cloud_contexts[] = $k8s_cluster['value'];
      }
    }
    elseif (!empty($cloud_context)) {
      $cloud_contexts[] = $cloud_context;
    }

    // If cloud_context is passed, filter entities with it.
    if (!empty($cloud_contexts)) {
      foreach ($cloud_contexts ?: [] as $cloud_context) {
        $nodes = array_merge($nodes, $this->entityTypeManager()
          ->getStorage('k8s_node')->loadByProperties(
          [
            'cloud_context' => $cloud_context,
          ]
        ));

        $namespaces = array_merge($namespaces, isset($k8s_namespace)
          ? $this->entityTypeManager()->getStorage('k8s_namespace')
            ->loadByProperties([
              'id' => $k8s_namespace->id(),
            ])
          : $this->entityTypeManager()->getStorage('k8s_namespace')
            ->loadByProperties([
              'cloud_context' => $cloud_context,
            ]));
      }
    }
    return !empty($cloud_project)
      ? $this->calculateNamespaceCosts($namespaces, $nodes)
      : $this->calculateNamespaceCosts($namespaces, $nodes, $cloud_context);
  }

  /**
   * Calculate namespace costs.
   *
   * @param array $namespaces
   *   Array of K8s Namespace entities.
   * @param array $nodes
   *   Array of K8s Node entities.
   * @param string $cloud_context
   *   Optional cloud context.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  private function calculateNamespaceCosts(array $namespaces, array $nodes, $cloud_context = NULL) {

    $cost_type = $this->request->get('cost_type');
    if (!isset($cost_type) || empty($cost_type)) {
      $cost_type = 'ri_one_year';
    }
    $period = $this->request->get('period');

    if (!isset($period) || empty($period)) {
      $period = 14;
    }
    $total_costs = $this->getTotalNodesCosts($nodes, $cost_type);

    $cpu_capacity = array_sum(array_map(static function ($node) {
      return $node->getCpuCapacity();
    }, $nodes));

    $memory_capacity = array_sum(array_map(static function ($node) {
      return $node->getMemoryCapacity();
    }, $nodes));

    $pod_capacity = array_sum(array_map(static function ($node) {
      return $node->getPodsCapacity();
    }, $nodes));

    $pod_metrics = $this->getAllPodsMetrics($cloud_context, $period);

    $response = [];
    foreach ($namespaces ?: [] as $namespace) {
      $pods = $this->entityTypeManager
        ->getStorage('k8s_pod')->loadByProperties(
          [
            'cloud_context' => $namespace->getCloudContext(),
            'namespace' => $namespace->getName(),
          ]
        );
      $data = [
        'namespace' => !empty($cloud_context) ? $namespace->getName() : $namespace->getCloudContext() . ':' . $namespace->getName(),
      ];
      foreach ($pod_metrics ?: [] as $time => $pod_datas) {
        $cpu_usage = (float) 0;
        $memory_usage = (float) 0;
        $pod_usage = count($pods);
        foreach ($pods ?: [] as $pod) {
          $key = $namespace->getName() . ':' . $pod->getName();
          if (isset($pod_datas[$key])) {
            $pod_data = $pod_datas[$key];
            $cpu_usage += $pod_data['cpu'];
            $memory_usage += $pod_data['memory'];
          }
        }
        $costs = ($cpu_usage / $cpu_capacity + $memory_usage / $memory_capacity + $pod_usage / $pod_capacity) / 3 * $total_costs;
        $data['costs'][] = [
          'timestamp' => $time,
          'cost' => $costs,
        ];
      }
      $response[] = $data;
    }

    return new JsonResponse($response);
  }

  /**
   * Get total costs of nodes.
   *
   * @param array $nodes
   *   The k8s_node entities.
   * @param string $cost_type
   *   The cost type.
   *
   * @return int
   *   The total costs of nodes.
   */
  private function getTotalNodesCosts(array $nodes, $cost_type = '') {
    $costs = 0;

    if (!$this->moduleHandler()->moduleExists('aws_cloud')) {
      return $costs;
    }

    $price_date_provider = \Drupal::service('aws_cloud.instance_type_price_data_provider');

    foreach ($nodes ?: [] as $node) {
      // Get instance type and region.
      $region = NULL;
      $instance_type = NULL;
      foreach ($node->get('labels') ?: [] as $item) {
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
            if ($cost_type === 'on_demand_hourly') {
              $costs += $item[$cost_type] * 24 * 365;
            }
            elseif ($cost_type === 'on_demand_daily') {
              $costs += $item[$cost_type] * 365;
            }
            elseif ($cost_type === 'on_demand_monthly') {
              $costs += $item[$cost_type] * 12;
            }
            elseif ($cost_type === 'on_demand_yearly') {
              $costs += $item[$cost_type];
            }
            elseif ($cost_type === 'ri_one_year') {
              $costs += $item[$cost_type];
            }
            elseif ($cost_type === 'ri_three_year') {
              $costs += $item[$cost_type] / 3;
            }
          }
          break;
        }
      }
    }

    return $costs;
  }

  /**
   * Get all pods metrics.
   *
   * @param string $cloud_context
   *   Cloud context string.  If no cloud context passed, retrieve
   *   all metric messages.
   * @param int $period
   *   The period to get data.
   *
   * @return array
   *   The pod metrics array.
   */
  private function getAllPodsMetrics($cloud_context = NULL, $period = 14) {
    $query = $this->database
      ->select('watchdog', 'w')
      ->fields('w', ['message', 'timestamp'])
      ->condition('type', 'k8s_metrics')
      ->condition('timestamp', time() - (int) $period * 24 * 60 * 60, '>=');

    // Add cloud_context if set.
    if (!empty($cloud_context)) {
      $query->condition('message', "%$cloud_context:%", 'LIKE');
    }

    $result = $query->condition('message', '%pods:%', 'LIKE')
      ->orderBy('timestamp')
      ->execute();
    $pod_metrics = [];
    foreach ($result ?: [] as $record) {
      $message = Yaml::decode($record->message);
      if (!empty($cloud_context)) {
        if (empty($message[$cloud_context])) {
          continue;
        }
        if (empty($message[$cloud_context]['pods'])) {
          continue;
        }
        $pods = $message[$cloud_context];
      }
      else {
        $pods = array_shift($message);
      }

      $pod_metrics[$record->timestamp] = [];
      foreach ($pods['pods'] ?: [] as $key => $pod_data) {
        $pod_metrics[$record->timestamp][$key] = [
          'cpu' => $pod_data['cpu'],
          'memory' => $pod_data['memory'] ?? 0,
        ];
      }
    }

    return $pod_metrics;
  }

  /**
   * Get a list of Ec2 cost Types such as On-demand, RI one or three years.
   *
   * @param string $json
   *   Whether the response should be json.
   *
   * @return array|\Symfony\Component\HttpFoundation\JsonResponse
   *   The list or JSON response of Ec2 cost types.
   */
  public function getEc2CostTypes($json = NULL) {
    $cost_types = [
      'on_demand_hourly'  => $this->t('On-demand Hourly'),
      'on_demand_daily'   => $this->t('On-demand Daily'),
      'on_demand_monthly' => $this->t('On-demand Monthly'),
      'on_demand_yearly'  => $this->t('On-demand Yearly'),
      'ri_one_year'       => $this->t('RI 1 Year'),
      'ri_three_year'     => $this->t('RI 3 Years'),
    ];
    if (isset($json)) {
      return new JsonResponse($cost_types);
    }
    else {
      return $cost_types;
    }
  }

  /**
   * Get a list of chart period.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param \Drupal\cloud\Entity\CloudProjectInterface $cloud_project
   *   The cloud project.
   * @param string $json
   *   Whether the response should be json.
   *
   * @return array|\Symfony\Component\HttpFoundation\JsonResponse
   *   The list or JSON response of chart period.
   */
  public function getEc2ChartPeriod($cloud_context = NULL, CloudProjectInterface $cloud_project = NULL, $json = NULL) {
    $cloud_contexts = [];

    if (!empty($cloud_project)) {
      $k8s_clusters = $cloud_project->get('field_k8s_clusters')->getValue();
      foreach ($k8s_clusters ?: [] as $k8s_cluster) {
        $cloud_contexts[] = $k8s_cluster['value'];
      }
    }
    elseif (!empty($cloud_context)) {
      $cloud_contexts[] = $cloud_context;
    }

    try {
      $query = $this->database->select('watchdog', 'w');
      $query->addExpression('min(w.timestamp)');
      if (!empty($cloud_contexts)) {
        $or_group = $query->orConditionGroup();
        foreach ($cloud_contexts ?: [] as $cloud_context) {
          $or_group->condition('message', "%$cloud_context%", 'LIKE');
        }
      }
      $timestamp = $query->condition('type', 'k8s_metrics')
        ->execute()->fetchField();
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }

    $period_option = [
      1 => $this->t('One day'),
      7 => $this->t('One week'),
      14 => $this->t('Two weeks'),
      31 => $this->t('One month'),
      183 => $this->t('Half a year'),
      365 => $this->t('One year'),
    ];
    $keys = array_keys($period_option);
    $keys = array_reverse($keys);
    foreach ($keys ?: [] as $idx => $day) {
      if ($day > 7 && (time() - $day * 24 * 60 * 60 <= $timestamp) && (time() - $keys[$idx + 1] * 24 * 60 * 60 <= $timestamp)) {
        unset($period_option[$day]);
      }
    }
    if (isset($json)) {
      return new JsonResponse($period_option);
    }
    else {
      return $period_option;
    }
  }

}
