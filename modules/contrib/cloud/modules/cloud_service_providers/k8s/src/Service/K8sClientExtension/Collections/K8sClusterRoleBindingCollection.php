<?php

namespace Drupal\k8s\Service\K8sClientExtension\Collections;

use Drupal\k8s\Service\K8sClientExtension\Models\K8sClusterRoleBindingModel;
use Maclof\Kubernetes\Collections\Collection;

/**
 * K8s cluster role bindings collection.
 */
class K8sClusterRoleBindingCollection extends Collection {

  /**
   * The constructor.
   *
   * @param array $items
   *   The items.
   */
  public function __construct(array $items) {
    parent::__construct($this->getClusterRoleBindings($items));
  }

  /**
   * Get an array of cluster role bindings.
   *
   * @param array $items
   *   The items.
   *
   * @return array
   *   The array of cluster roles.
   */
  protected function getClusterRoleBindings(array $items) {
    foreach ($items ?: [] as &$item) {
      if ($item instanceof K8sClusterRoleBindingModel) {
        continue;
      }

      $item = new K8sClusterRoleBindingModel($item);
    }

    return $items;
  }

}
