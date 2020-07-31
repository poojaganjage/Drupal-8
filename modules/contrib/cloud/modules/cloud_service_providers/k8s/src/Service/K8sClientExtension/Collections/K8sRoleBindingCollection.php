<?php

namespace Drupal\k8s\Service\K8sClientExtension\Collections;

use Drupal\k8s\Service\K8sClientExtension\Models\K8sRoleBindingModel;
use Maclof\Kubernetes\Collections\Collection;

/**
 * K8s role bindings collection.
 */
class K8sRoleBindingCollection extends Collection {

  /**
   * The constructor.
   *
   * @param array $items
   *   The items.
   */
  public function __construct(array $items) {
    parent::__construct($this->getRoleBindings($items));
  }

  /**
   * Get an array of role bindings.
   *
   * @param array $items
   *   The items.
   *
   * @return array
   *   The array of roles.
   */
  protected function getRoleBindings(array $items) {
    foreach ($items ?: [] as &$item) {
      if ($item instanceof K8sRoleBindingModel) {
        continue;
      }

      $item = new K8sRoleBindingModel($item);
    }

    return $items;
  }

}
