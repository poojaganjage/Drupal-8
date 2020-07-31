<?php

namespace Drupal\k8s\Service\K8sClientExtension\Collections;

use Drupal\k8s\Service\K8sClientExtension\Models\K8sClusterRoleModel;
use Maclof\Kubernetes\Collections\Collection;

/**
 * K8s cluster roles collection.
 */
class K8sClusterRoleCollection extends Collection {

  /**
   * The constructor.
   *
   * @param array $items
   *   The items.
   */
  public function __construct(array $items) {
    parent::__construct($this->getClusterRoles($items));
  }

  /**
   * Get an array of cluster roles.
   *
   * @param array $items
   *   The items.
   *
   * @return array
   *   The array of cluster roles.
   */
  protected function getClusterRoles(array $items) {
    foreach ($items ?: [] as &$item) {
      if ($item instanceof K8sClusterRoleModel) {
        continue;
      }

      $item = new K8sClusterRoleModel($item);
    }

    return $items;
  }

}
