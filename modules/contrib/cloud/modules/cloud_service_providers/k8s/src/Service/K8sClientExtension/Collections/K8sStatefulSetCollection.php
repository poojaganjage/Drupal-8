<?php

namespace Drupal\k8s\Service\K8sClientExtension\Collections;

use Drupal\k8s\Service\K8sClientExtension\Models\K8sStatefulSetModel;
use Maclof\Kubernetes\Collections\Collection;

/**
 * K8s stateful sets collection.
 */
class K8sStatefulSetCollection extends Collection {

  /**
   * The constructor.
   *
   * @param array $items
   *   The items.
   */
  public function __construct(array $items) {
    parent::__construct($this->getStatefulSets($items));
  }

  /**
   * Get an array of stateful sets.
   *
   * @param array $items
   *   The items.
   *
   * @return array
   *   The array of stateful sets.
   */
  protected function getStatefulSets(array $items) {
    foreach ($items ?: [] as &$item) {
      if ($item instanceof K8sStatefulSetModel) {
        continue;
      }

      $item = new K8sStatefulSetModel($item);
    }

    return $items;
  }

}
