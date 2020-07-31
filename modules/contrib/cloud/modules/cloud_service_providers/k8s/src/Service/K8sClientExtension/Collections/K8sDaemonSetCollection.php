<?php

namespace Drupal\k8s\Service\K8sClientExtension\Collections;

use Drupal\k8s\Service\K8sClientExtension\Models\K8sDaemonSetModel;
use Maclof\Kubernetes\Collections\Collection;

/**
 * K8s daemon sets collection.
 */
class K8sDaemonSetCollection extends Collection {

  /**
   * The constructor.
   *
   * @param array $items
   *   The items.
   */
  public function __construct(array $items) {
    parent::__construct($this->getDaemonSets($items));
  }

  /**
   * Get an array of daemon sets.
   *
   * @param array $items
   *   The items.
   *
   * @return array
   *   The array of daemon sets.
   */
  protected function getDaemonSets(array $items) {
    foreach ($items ?: [] as &$item) {
      if ($item instanceof K8sDaemonSetModel) {
        continue;
      }

      $item = new K8sDaemonSetModel($item);
    }

    return $items;
  }

}
