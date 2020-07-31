<?php

namespace Drupal\k8s\Service\K8sClientExtension\Collections;

use Drupal\k8s\Service\K8sClientExtension\Models\K8sMetricsNodeModel;
use Maclof\Kubernetes\Collections\Collection;

/**
 * K8s metrics nodes collection.
 */
class K8sMetricsNodeCollection extends Collection {

  /**
   * The constructor.
   *
   * @param array $items
   *   The items.
   */
  public function __construct(array $items) {
    parent::__construct($this->getMetricsNodes($items));
  }

  /**
   * Get an array of metrics nodes.
   *
   * @param array $items
   *   The items.
   *
   * @return array
   *   The array of metrics nodes.
   */
  protected function getMetricsNodes(array $items) {
    foreach ($items ?: [] as &$item) {
      if ($item instanceof K8sMetricsNodeModel) {
        continue;
      }

      $item = new K8sMetricsNodeModel($item);
    }

    return $items;
  }

}
