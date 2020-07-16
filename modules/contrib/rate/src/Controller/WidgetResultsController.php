<?php

namespace Drupal\rate\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;

/**
 * Returns responses for Rate routes.
 */
class WidgetResultsController extends ControllerBase {

  /**
   * Display rate voting results views on nodes.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node for which to display results.
   *
   * @return array
   *   The render array.
   */
  public function nodeResults(NodeInterface $node) {
    $entity_id = $node->id();
    $entity_type_id = $node->getEntityTypeId();
    $bundle = $node->bundle();
    // First, make sure the data is fresh.
    $cache_bins = Cache::getBins();
    $cache_bins['data']->deleteAll();

    // Check if the node has widgets enabled.
    $widgets = \Drupal::service('entity_type.manager')->getStorage('rate_widget')->loadMultiple();
    if (!empty($widgets)) {
      foreach ($widgets as $widget => $widget_variables) {
        $entities = $widget_variables->get('entity_types');
        if ($entities && count($entities) > 0) {
          foreach ($entities as $id => $entity) {
            if ($entity == $entity_type_id . '.' . $bundle) {
              // Get and return the rate results views.
              $page[] = ['#type' => '#markup', '#markup' => '<strong>' . $widget_variables->label() . '</strong>'];
              $page[] = views_embed_view('rate_widgets_results', 'node_summary_block', $node->id(), $node->getEntityTypeId(), $widget);
              $page[] = views_embed_view('rate_widgets_results', 'node_results_block', $node->id(), $node->getEntityTypeId(), $widget);
            }
          }
        }
      }
    }
    return $page;
  }

}
