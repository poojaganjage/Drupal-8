<?php

namespace Drupal\k8s\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a block of the allocated resources at a Node.
 *
 * @Block(
 *   id = "k8s_node_allocated_resources",
 *   admin_label = @Translation("K8s Node Allocated Resources"),
 *   category = @Translation("K8s")
 * )
 */
class K8sNodeAllocatedResourcesBlock extends K8sBaseBlock {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'display_view_k8s_node_list_only' => 1,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['display_view_k8s_node_list_only'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display this block in K8s Node list page and K8s Project Content page only'),
      '#default_value' => $this->configuration['display_view_k8s_node_list_only'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['display_view_k8s_node_list_only']
      = $form_state->getValue('display_view_k8s_node_list_only');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    if ($this->isK8sEmpty() === TRUE) {
      return [];
    }

    // If $cloud_context is empty, do nothing.
    $cloud_context = $this->routeMatch->getParameter('cloud_context');

    // If display_view_k8s_node_list_only is checked, do nothing.
    $route_name = $this->routeMatch->getRouteName();
    if ($route_name !== 'view.k8s_node.list'
      && $route_name !== 'entity.cloud_project.canonical'
      && $this->configuration['display_view_k8s_node_list_only']) {
      return [];
    }

    $cloud_project = $this->routeMatch->getParameter('cloud_project');

    $metrics_enabled = FALSE;
    if (!empty($cloud_project)) {
      // Confirm whether the metrics API can be used or not.
      $k8s_clusters = $cloud_project->get('field_k8s_clusters')->getValue();
      foreach ($k8s_clusters ?: [] as $k8s_cluster) {
        $this->cloudConfigPluginManager->setCloudContext($k8s_cluster['value']);
        $cloud_config = $this->cloudConfigPluginManager->loadConfigEntity();
        $message = count($k8s_clusters) > 1
          ? $this->t('Node Allocated Resources block requires K8s Metrics Server for %cloud_config.', ['%cloud_config' => $cloud_config->link()])
          : $this->t('Node Allocated Resources block requires K8s Metrics Server.');
        if ($this->isMetricsServerEnabled($k8s_cluster['value'] ?? '', $message)) {
          $metrics_enabled = TRUE;
        }
      }
    }
    else {
      // The following $cloud_context is reference to view.k8s_node.list, not
      // entity.k8s_node.collection in out case.  See also:
      // ...
      // $this->derivatives[$id]['route_name'] = 'view.k8s_node.list';
      //
      // _OR_
      //
      // K8sLocalTask::getDerivativeDefinitions($base_plugin_definition) {
      // ...
      // $this->derivatives[$id]['route_name'] = 'view.k8s_node.list';
      // ...
      // Confirm whether the metrics API can be used or not.
      // This method handles cases where there are no cloud_context passed.
      // This usually happens when the block is called from a dashboard page.
      $metrics_enabled = $this->isMetricsServerEnabled(
        $cloud_context ?? '',
        $this->t('Node Allocated Resources block requires K8s Metrics Server.')
      );
    }

    $fieldset_defs = [
      [
        'name' => 'k8s_allocated_resources',
        'title' => $this->t('Allocated Resources'),
        'open' => TRUE,
        'fields' => [
          'k8s_node_allocated_resources',
        ],
      ],
    ];

    $build['k8s_node_allocated_resources'] = [
      '#markup' => '<div id="k8s_node_allocated_resources"></div>',
      '#attached' => [
        'library' => [
          'k8s/k8s_node_allocated_resources',
        ],
        'drupalSettings' => [
          'k8s' => [
            'k8s_js_refresh_interval' => $this->configFactory->get('k8s.settings')
              ->get('k8s_js_refresh_interval'),
            'metrics_enabled' => $metrics_enabled,
            'resource_url' => k8s_get_resource_url(),
          ],
        ],
      ],
    ];

    $this->cloudService->reorderForm($build, $fieldset_defs);

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

}
