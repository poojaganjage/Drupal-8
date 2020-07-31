<?php

namespace Drupal\k8s\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\k8s\Entity\K8sNode;

/**
 * Provides a block of the costs.
 *
 * @Block(
 *   id = "k8s_node_costs",
 *   admin_label = @Translation("K8s Node Costs"),
 *   category = @Translation("K8s")
 * )
 */
class K8sNodeCostsBlock extends K8sBaseBlock {

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

    // If display_view_k8s_node_list_only is checked, do nothing.
    $route_name = $this->routeMatch->getRouteName();
    if ($route_name !== 'view.k8s_node.list'
      && $this->configuration['display_view_k8s_node_list_only']) {
      return [];
    }

    $entities = [];
    $cloud_context = $this->routeMatch->getParameter('cloud_context');
    if (!empty($cloud_context)) {
      $entities = $this->entityTypeManager->getStorage('k8s_node')->loadByProperties(
        ['cloud_context' => [$cloud_context]]
      );
      if (empty($entities)) {
        $this->setUpdateMessage($cloud_context, $this->t('Nodes not found for K8s Node Cost block.'));
      }
    }
    else {
      $ids = $this->entityTypeManager->getStorage('k8s_node')
        ->getQuery()
        ->execute();
      foreach ($ids ?: [] as $key => $entity) {
        $entities[] = K8sNode::load($key);
      }
    }
    // Get instance type and region.
    $region = NULL;
    $instance_types = [];
    foreach ($entities ?: [] as $entity) {
      foreach ($entity->get('labels') ?: [] as $item) {
        if ($item->getItemKey() === 'beta.kubernetes.io/instance-type') {
          $instance_types[] = $item->getItemValue();
        }
        elseif ($item->getItemKey() === 'failure-domain.beta.kubernetes.io/region') {
          $region = $item->getItemValue();
        }
      }
    }

    $fieldset_defs = [
      [
        'name' => 'k8s_costs',
        'title' => $this->t('Costs'),
        'open' => TRUE,
        'fields' => [
          'k8s_node_costs',
        ],
      ],
    ];

    $build['k8s_node_costs'] = $this->costFieldsRenderer->render($region, $instance_types);

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
