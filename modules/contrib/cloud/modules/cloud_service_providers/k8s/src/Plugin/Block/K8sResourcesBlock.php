<?php

namespace Drupal\k8s\Plugin\Block;

use Drupal\cloud\Traits\ResourceBlockTrait;
use Drupal\Core\Link;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cloud\Entity\CloudProjectInterface;

/**
 * Provides a resource block.
 *
 * @Block(
 *   id = "k8s_resources_block",
 *   admin_label = @Translation("K8s Resources"),
 *   category = @Translation("K8s")
 * )
 */
class K8sResourcesBlock extends K8sBaseBlock {

  use ResourceBlockTrait;

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['cloud_context'] = [
      '#type' => 'select',
      '#title' => $this->t('Cloud Service Provider'),
      '#description' => $this->t('Select cloud service provider.<br/>On the K8s Project Content page, resources of k8s clusters are displayed regardless of this value.'),
      '#options' => $this->getCloudConfigs($this->t('All K8s providers'), 'k8s'),
      '#default_value' => $config['cloud_context'] ?? '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['cloud_context'] = $form_state->getValue('cloud_context');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'cloud_context' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $cloud_configs = $this->getCloudConfigs($this->t('All K8s providers'), 'k8s');
    $cloud_context = $this->configuration['cloud_context'];
    $cloud_project = $this->routeMatch->getParameter('cloud_project');

    if (!empty($cloud_project)) {
      if (!$cloud_project instanceof CloudProjectInterface) {
        $cloud_project = $this->entityTypeManager->getStorage('cloud_project')->load($cloud_project);
      }
      $k8s_clusters = $cloud_project->get('field_k8s_clusters')->getValue();
      $cloud_contexts = [];
      foreach ($k8s_clusters ?: [] as $k8s_cluster) {
        $cloud_contexts[] = $cloud_configs[$k8s_cluster['value']];
      }
      $cloud_context_name = implode(', ', $cloud_contexts);
    }
    else {
      $cloud_context_name = empty($cloud_context)
        ? 'all K8s providers'
        : $cloud_configs[$cloud_context];
    }

    $build = [];
    $build['resources'] = [
      '#type' => 'details',
      '#title' => $this->t('Resources'),
      '#open' => TRUE,
    ];
    $build['resources']['description'] = [
      '#markup' => $this->t(
        'You are using the following K8s resources in %cloud_context_name:',
        ['%cloud_context_name' => $cloud_context_name]
      ),
    ];

    $build['resources']['resource_table'] = $this->buildResourceTable();
    return $build;
  }

  /**
   * Build a resource HTML table.
   *
   * @return array
   *   Table array.
   */
  private function buildResourceTable() {
    $resources = [
      'k8s_node' => [
        'view any k8s node',
        [],
      ],
      'k8s_namespace' => [
        'view any k8s namespace',
        [],
      ],
      'k8s_deployment' => [
        'view any k8s deployment',
        [],
      ],
      'k8s_pod' => [
        'view any k8s pod',
        [],
      ],
      'k8s_replica_set' => [
        'view any k8s replica set',
        [],
      ],
      'k8s_cron_job' => [
        'view any k8s cron job',
        [],
      ],
      'k8s_job' => [
        'view any k8s job',
        [],
      ],
      'k8s_service' => [
        'view any k8s service',
        [],
      ],
      'k8s_network_policy' => [
        'view any k8s network policy',
        [],
      ],
      'k8s_resource_quota' => [
        'view any k8s resource quota',
        [],
      ],
      'k8s_limit_range' => [
        'view any k8s limit range',
        [],
      ],
      'k8s_config_map' => [
        'view any k8s configmap',
        [],
      ],
      'k8s_secret' => [
        'view any k8s secret',
        [],
      ],
      'k8s_role' => [
        'view any k8s role',
        [],
      ],
      'k8s_role_binding' => [
        'view any k8s role binding',
        [],
      ],
      'k8s_cluster_role' => [
        'view any k8s cluster role',
        [],
      ],
      'k8s_cluster_role_binding' => [
        'view any k8s cluster role bindings',
        [],
      ],
      'k8s_persistent_volume' => [
        'view any k8s persistent volume',
        [],
      ],
      'k8s_persistent_volume_claim' => [
        'view any k8s persistent volume claim',
        [],
      ],
      'k8s_storage_class' => [
        'view any k8s storage class',
        [],
      ],
      'k8s_stateful_set' => [
        'view any k8s stateful set',
        [],
      ],
      'k8s_ingress' => [
        'view any k8s ingress',
        [],
      ],
      'k8s_daemon_set' => [
        'view any k8s daemon set',
        [],
      ],
      'k8s_endpoint' => [
        'view any k8s endpoint',
        [],
      ],
      'k8s_event' => [
        'view any k8s event',
        [],
      ],
      'k8s_api_service' => [
        'view any k8s api service',
        [],
      ],
      'k8s_service_account' => [
        'view any k8s service account',
        [],
      ],
      'k8s_priority_class' => [
        'view any k8s priority class',
        [],
      ],
    ];

    $rows = $this->buildResourceTableRows($resources);

    return [
      '#type' => 'table',
      '#rows' => $rows,
    ];
  }

  /**
   * Generate K8s resource link.
   *
   * @param string $resource_type
   *   The resource type.
   * @param string $permission
   *   The getResourceCount permission.
   * @param array $params
   *   The getResourceCount params.
   *
   * @return \Drupal\Core\Link
   *   The K8s resource link.
   */
  protected function getResourceLink($resource_type, $permission, array $params = []) {
    // Fetch the labels.
    $labels = $this->getDisplayLabels($resource_type);

    $cloud_project = $this->routeMatch->getParameter('cloud_project');
    $route_name = $this->routeMatch->getRouteName();

    if (!empty($cloud_project)) {
      if (!$cloud_project instanceof CloudProjectInterface) {
        $cloud_project = $this->entityTypeManager->getStorage('cloud_project')->load($cloud_project);
      }
      $k8s_clusters = $cloud_project->get('field_k8s_clusters')->getValue();

      $count = 0;
      foreach ($k8s_clusters ?: [] as $k8s_cluster) {
        $params = [
          'cloud_context' => $k8s_cluster['value'],
        ];
        $count += $this->getResourceCount($resource_type, $permission, $params);
      }
      $route_name = "view.${resource_type}.project";
      $params = [
        'cloud_context' => $cloud_project->getCloudContext(),
        'cloud_project' => $cloud_project->id(),
      ];
      return Link::createFromRoute(
        $this->formatPlural(
          $count,
          '1 @label',
          '@count @plural_label',
          [
            '@label' => $labels['singular'] ?? $resource_type,
            '@plural_label' => $labels['plural'] ?? $resource_type,
          ]
        ),
        $route_name,
        $params
      );
    }
    else {
      $cloud_context = $this->configuration['cloud_context'];

      if (!empty($cloud_context)) {
        $route_name = "view.${resource_type}.list";
        $params = [
          'cloud_context' => $cloud_context,
        ];
      }
      else {
        $route_name = "view.${resource_type}.all";
      }

      return Link::createFromRoute(
      $this->formatPlural(
        $this->getResourceCount($resource_type, $permission, $params),
          '1 @label',
          '@count @plural_label',
          [
            '@label' => $labels['singular'] ?? $resource_type,
            '@plural_label' => $labels['plural'] ?? $resource_type,
          ]
        ),
        $route_name,
        $params
      );
    }
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

}
