<?php

namespace Drupal\k8s\Service\K8sClientExtension\Collections;

use Drupal\k8s\Service\K8sClientExtension\Models\K8sStorageClassModel;
use Maclof\Kubernetes\Collections\Collection;

/**
 * K8s storage classes collection.
 */
class K8sStorageClassCollection extends Collection {

  /**
   * The constructor.
   *
   * @param array $items
   *   The items.
   */
  public function __construct(array $items) {
    parent::__construct($this->getStorageClasses($items));
  }

  /**
   * Get an array of storage classes.
   *
   * @param array $items
   *   The items.
   *
   * @return array
   *   The array of storage classes.
   */
  protected function getStorageClasses(array $items) {
    foreach ($items ?: [] as &$item) {
      if ($item instanceof K8sStorageClassModel) {
        continue;
      }

      $item = new K8sStorageClassModel($item);
    }

    return $items;
  }

}
