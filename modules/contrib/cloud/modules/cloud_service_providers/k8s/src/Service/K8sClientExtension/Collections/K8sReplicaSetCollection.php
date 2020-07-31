<?php

namespace Drupal\k8s\Service\K8sClientExtension\Collections;

use Drupal\k8s\Service\K8sClientExtension\Models\K8sReplicaSetModel;
use Maclof\Kubernetes\Collections\Collection;

/**
 * K8s replica sets collection.
 */
class K8sReplicaSetCollection extends Collection {

  /**
   * The constructor.
   *
   * @param array $items
   *   The items.
   */
  public function __construct(array $items) {
    parent::__construct($this->getReplicaSets($items));
  }

  /**
   * Get an array of replica sets.
   *
   * @param array $items
   *   The items.
   *
   * @return array
   *   The array of replica sets.
   */
  protected function getReplicaSets(array $items) {
    foreach ($items ?: [] as &$item) {
      if ($item instanceof K8sReplicaSetModel) {
        continue;
      }

      $item = new K8sReplicaSetModel($item);
    }

    return $items;
  }

}
