<?php

namespace Drupal\k8s\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\k8s\Entity\K8sNamespace;
use Drupal\k8s\Entity\K8sNode;

/**
 * Provides a block of the costs.
 *
 * @Block(
 *   id = "k8s_namespace_costs",
 *   admin_label = @Translation("K8s Namespace Costs"),
 *   category = @Translation("K8s")
 * )
 */
class K8sNamespaceCostsBlock extends K8sBaseBlock {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'display_view_k8s_namespace_list_only' => 1,
      'aws_cloud_ec2_cost_type' => 'ri_one_year',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['display_view_k8s_namespace_list_only'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display this block in K8s Namespace list page and K8s Project Content page only'),
      '#default_value' => $this->configuration['display_view_k8s_namespace_list_only'],
    ];

    $form['aws_cloud_ec2_cost_type'] = [
      '#type' => 'select',
      '#title' => $this->t('AWS Cloud EC2 Cost Type'),
      '#options' => $this->getEc2CostTypes(),
      '#default_value' => $this->configuration['aws_cloud_ec2_cost_type'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {

    $this->configuration['display_view_k8s_namespace_list_only']
      = $form_state->getValue('display_view_k8s_namespace_list_only');

    $this->configuration['aws_cloud_ec2_cost_type']
      = $form_state->getValue('aws_cloud_ec2_cost_type');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    // If aws_cloud module is not enabled; do nothing.
    if (!$this->moduleHandler->moduleExists('aws_cloud')) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('This block requires the AWS Cloud
        module to be enabled.'),
      ];
    }

    if ($this->isK8sEmpty() === TRUE) {
      return [];
    }

    // If display_view_k8s_namespace_list_only is checked, do nothing.
    $route_name = $this->routeMatch->getRouteName();
    if ($route_name !== 'view.k8s_namespace.list'
      && $route_name !== 'entity.cloud_project.canonical'
      && $this->configuration['display_view_k8s_namespace_list_only']) {
      return [];
    }

    $nodes = [];
    $namespaces = [];
    $cloud_contexts = [];

    $cloud_context = $this->routeMatch->getParameter('cloud_context');
    $cloud_project = $this->routeMatch->getParameter('cloud_project');

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
        $nodes = array_merge($nodes, $this->entityTypeManager
          ->getStorage('k8s_node')->loadByProperties(
            [
              'cloud_context' => $cloud_context,
            ]
          ));

        $namespaces = array_merge($namespaces, $this->entityTypeManager
          ->getStorage('k8s_namespace')->loadByProperties(
            [
              'cloud_context' => $cloud_context,
            ]
          ));
      }

      // If nodes or namespaces are empty, ask the user
      // to try updating the cloud_context to bring in
      // the latest data.
      if (empty($nodes) || empty($namespaces)) {
        $message = $this->t('Nodes and/or namespaces not found for K8s Namespace Cost block.');
        $this->setUpdateMessage($cloud_context, $message);
      }
    }
    else {
      // Load all nodes and namespaces.
      $node_ids = $this->entityTypeManager
        ->getStorage('k8s_node')->getQuery()
        ->execute();
      foreach ($node_ids ?: [] as $key => $node) {
        $nodes[] = K8sNode::load($key);
      }
      $namespace_ids = $this->entityTypeManager
        ->getStorage('k8s_namespace')->getQuery()
        ->execute();
      foreach ($namespace_ids ?: [] as $key => $namespace) {
        $namespaces[] = K8sNamespace::load($key);
      }
    }

    $build = [];

    $total_costs = $this->k8sService->getTotalCosts($nodes);

    // $cpu_capacity = $this->getCpuCapacity($nodes);
    $cpu_capacity = array_sum(array_map(static function ($node) {
      return $node->getCpuCapacity();
    }, $nodes));

    $memory_capacity = array_sum(array_map(static function ($node) {
      return $node->getMemoryCapacity();
    }, $nodes));

    $pod_capacity = array_sum(array_map(static function ($node) {
      return $node->getPodsCapacity();
    }, $nodes));

    // Get row data.
    $rows = [];
    foreach ($namespaces ?: [] as $namespace) {
      $pods = $this->entityTypeManager
        ->getStorage('k8s_pod')->loadByProperties([
          'cloud_context' => $namespace->getCloudContext(),
          'namespace' => $namespace->getName(),
        ]
      );

      $row = [];
      if (!empty($cloud_project)) {
        $this->cloudConfigPluginManager->setCloudContext($namespace->getCloudContext());
        $cloud_config = $this->cloudConfigPluginManager->loadConfigEntity();
        $route = $this->cloudConfigPluginManager->getInstanceCollectionTemplateName();

        $row['k8s_cluster'] = [
          'data' => [
            '#type' => 'link',
            '#url' => Url::fromRoute($route, ['cloud_context' => $namespace->getCloudContext()]),
            '#title' => $cloud_config->getName(),
          ],
        ];
      }

      $row['namespace'] = [
        'data' => [
          '#type' => 'link',
          '#title' => $namespace->getName(),
          '#url' => Url::fromRoute(
            'entity.k8s_namespace.canonical',
            [
              'cloud_context' => $namespace->getCloudContext(),
              'k8s_namespace' => $namespace->id(),
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

      $costs = $total_costs > 0 ? ($cpu_usage / $cpu_capacity + $memory_usage / $memory_capacity + $pod_usage / $pod_capacity) / 3 * $total_costs : 0;

      $row['costs'] = $total_costs > 0 ? $this->k8sService->formatCosts($costs, $total_costs) : 0;
      $row['cpu_usage'] = $cpu_capacity > 0 ? $this->k8sService->formatCpuUsage($cpu_usage, $cpu_capacity) : 0;
      $row['memory_usage'] = $memory_capacity > 0 ? $this->k8sService->formatMemoryUsage($memory_usage, $memory_capacity) : 0;
      $row['pod_usage'] = $pod_capacity > 0 ? $this->k8sService->formatPodUsage($pod_usage, $pod_capacity) : 0;

      $rows[] = $row;
    }

    $headers = [];

    if (!empty($cloud_project)) {
      $headers['k8s_cluster'] = ['data' => $this->t('K8s Cluster'), 'field' => 'k8s_cluster'];
    }

    $headers += [
        ['data' => $this->t('Namespace'), 'field' => 'namespace'],
        ['data' => $this->t('Total Costs ($)'), 'field' => 'costs'],
        ['data' => $this->t('CPU (Usage)'), 'field' => 'cpu_usage'],
        ['data' => $this->t('Memory (Usage)'), 'field' => 'memory_usage'],
        ['data' => $this->t('Pods Allocation'), 'field' => 'pod_usage'],
    ];

    $sort = $this->request->get('sort');
    $order = $this->request->get('order');

    $order_field = NULL;
    foreach ($headers ?: [] as $header) {
      if ($header['data']->render() === $order) {
        $order_field = $header['field'];
      }
    }

    // Get sort and order parameters.
    if (empty($sort)) {
      $sort = 'asc';
    }
    if (empty($order_field)) {
      if (!empty($cloud_project)) {
        $order_field = 'k8s_cluster';
      }
      else {
        $order_field = 'namespace';
      }
    }

    // Sort data.
    usort($rows, static function ($a, $b) use ($sort, $order_field) {
      $result = 1;
      if ($sort === 'desc') {
        $result *= -1;
      }

      if ($order_field === 'k8s_cluster' && $a[$order_field]['data']['#title'] === $b[$order_field]['data']['#title']) {
        $order_field = 'namespace';
        $result = 1;
      }

      if ($order_field === 'k8s_cluster' || $order_field === 'namespace') {
        $result *= $a[$order_field]['data']['#title'] < $b[$order_field]['data']['#title'] ? -1 : 1;
      }
      else {
        $match_a = [];
        $match_b = [];
        preg_match('/\d+(\.\d+)?%/', $a[$order_field], $match_a);
        preg_match('/\d+(\.\d+)?%/', $b[$order_field], $match_b);
        $num_a = filter_var($match_a[0], FILTER_SANITIZE_NUMBER_FLOAT) * 100;
        $num_b = filter_var($match_b[0], FILTER_SANITIZE_NUMBER_FLOAT);
        $result *= $num_a < $num_b ? -1 : 1;
      }

      return $result;
    });

    $table = [
      '#theme' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
    ];

    $cost_types = $this->getEc2CostTypes();
    $cost_type = $this->configuration['aws_cloud_ec2_cost_type'];
    $build['k8s_namespace_costs'] = [
      '#type' => 'details',
      '#title' => $this->t('Namespace Costs') . " ($cost_types[$cost_type])",
      '#open' => TRUE,
    ];

    $build['k8s_namespace_costs'][] = $table;

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    $this->killSwitch->trigger();
    // BigPipe/#cache/max-age is breaking my block javascript
    // https://www.drupal.org/forum/support/module-development-and-code-questions/2016-07-17/bigpipecachemax-age-is-breaking-my
    // "a slight delay of a second or two before the charts are built.
    // That seems to work, though it is a janky solution.".
    return $this->moduleHandler->moduleExists('big_pipe') ? 1 : 0;
  }

  /**
   * Get a list of Ec2 cost Types such as On-demand, RI one or three years.
   *
   * @return array
   *   The list of Ec2 cost Types.
   */
  private function getEc2CostTypes() {
    return [
      'on_demand_yearly'  => $this->t('On-demand Yearly'),
      'ri_one_year'       => $this->t('RI 1 Year'),
      'ri_three_year'     => $this->t('RI 3 Years'),
    ];
  }

}
