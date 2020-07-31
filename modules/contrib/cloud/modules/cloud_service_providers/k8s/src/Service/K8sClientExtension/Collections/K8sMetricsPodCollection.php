<?php

namespace Drupal\k8s\Service\K8sClientExtension\Collections;

use Drupal\k8s\Service\K8sClientExtension\Models\K8sMetricsPodModel;
use Maclof\Kubernetes\Collections\Collection;

/**
 * K8s metrics pods collection.
 */
class K8sMetricsPodCollection extends Collection {

  /**
   * The constructor.
   *
   * @param array $items
   *   The items.
   */
  public function __construct(array $items) {
    parent::__construct($this->getMetricsPods($items));
  }

  /**
   * Get an array of metrics pods.
   *
   * @param array $items
   *   The items.
   *
   * @return array
   *   The array of metrics pods.
   */
  protected function getMetricsPods(array $items) {
    foreach ($items ?: [] as &$item) {
      if ($item instanceof K8sMetricsPodModel) {
        continue;
      }

      $item = new K8sMetricsPodModel($item);
    }

    return $items;
  }

}
