<?php

namespace Drupal\k8s\Service\K8sClientExtension\Collections;

use Drupal\k8s\Service\K8sClientExtension\Models\K8sLimitRangeModel;
use Maclof\Kubernetes\Collections\Collection;

/**
 * K8s limit ranges collection.
 */
class K8sLimitRangeCollection extends Collection {

  /**
   * The constructor.
   *
   * @param array $items
   *   The items.
   */
  public function __construct(array $items) {
    parent::__construct($this->getLimitRanges($items));
  }

  /**
   * Get an array of limit ranges.
   *
   * @param array $items
   *   The items.
   *
   * @return array
   *   The array of limit ranges.
   */
  protected function getLimitRanges(array $items) {
    foreach ($items ?: [] as &$item) {
      if ($item instanceof K8sLimitRangeModel) {
        continue;
      }

      $item = new K8sLimitRangeModel($item);
    }

    return $items;
  }

}
