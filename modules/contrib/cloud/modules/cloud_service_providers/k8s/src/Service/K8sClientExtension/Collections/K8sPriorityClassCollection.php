<?php

namespace Drupal\k8s\Service\K8sClientExtension\Collections;

use Drupal\k8s\Service\K8sClientExtension\Models\K8sPriorityClassModel;
use Maclof\Kubernetes\Collections\Collection;

/**
 * K8s priority classes collection.
 */
class K8sPriorityClassCollection extends Collection {

  /**
   * The constructor.
   *
   * @param array $items
   *   The items.
   */
  public function __construct(array $items) {
    parent::__construct($this->getPriorityClasses($items));
  }

  /**
   * Get an array of priority classes.
   *
   * @param array $items
   *   The items.
   *
   * @return array
   *   The array of roles.
   */
  protected function getPriorityClasses(array $items) {
    foreach ($items ?: [] as &$item) {
      if ($item instanceof K8sPriorityClassModel) {
        continue;
      }

      $item = new K8sPriorityClassModel($item);
    }

    return $items;
  }

}
