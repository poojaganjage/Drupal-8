<?php

namespace Drupal\k8s\Service\K8sClientExtension\Collections;

use Drupal\k8s\Service\K8sClientExtension\Models\K8sRoleModel;
use Maclof\Kubernetes\Collections\Collection;

/**
 * K8s roles collection.
 */
class K8sRoleCollection extends Collection {

  /**
   * The constructor.
   *
   * @param array $items
   *   The items.
   */
  public function __construct(array $items) {
    parent::__construct($this->getRoles($items));
  }

  /**
   * Get an array of roles.
   *
   * @param array $items
   *   The items.
   *
   * @return array
   *   The array of roles.
   */
  protected function getRoles(array $items) {
    foreach ($items ?: [] as &$item) {
      if ($item instanceof K8sRoleModel) {
        continue;
      }

      $item = new K8sRoleModel($item);
    }

    return $items;
  }

}
