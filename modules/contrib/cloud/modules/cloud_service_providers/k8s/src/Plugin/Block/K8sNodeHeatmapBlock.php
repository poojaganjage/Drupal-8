<?php

namespace Drupal\k8s\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Node heatmap block.
 *
 * @Block(
 *   id = "k8s_node_heatmap",
 *   admin_label = @Translation("K8s Node Heatmap"),
 *   category = @Translation("K8s")
 * )
 */
class K8sNodeHeatmapBlock extends K8sBaseBlock {

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
      '#title' => $this->t('Display this block in K8s Node list page only'),
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

    $cloud_context = $this->routeMatch->getParameter('cloud_context');

    // If display_view_k8s_node_list_only is checked, do nothing.
    $route_name = $this->routeMatch->getRouteName();
    if ($route_name !== 'view.k8s_node.list'
    && $this->configuration['display_view_k8s_node_list_only']) {
      return [];
    }

    // Confirm whether the metrics API can be used or not.
    $metrics_enabled = $this->isMetricsServerEnabled(
      $cloud_context ?? '',
      $this->t('Node Heatmap block requires K8s Metrics Server.')
    );

    $fieldset_defs = [
      [
        'name' => 'k8s_heatmap',
        'title' => $this->t('Heatmap'),
        'open' => TRUE,
        'fields' => [
          'k8s_node_heatmap',
        ],
      ],
    ];

    $build['k8s_node_heatmap'] = [
      '#markup' => '<div id="k8s_node_heatmap"></div>',
      '#attached' => [
        'library' => [
          'k8s/k8s_node_heatmap',
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
