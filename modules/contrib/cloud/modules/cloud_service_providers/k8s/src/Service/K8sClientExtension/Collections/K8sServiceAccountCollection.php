<?php

namespace Drupal\k8s\Service\K8sClientExtension\Collections;

use Drupal\k8s\Service\K8sClientExtension\Models\K8sServiceAccountModel;
use Maclof\Kubernetes\Collections\Collection;

/**
 * K8s service accounts collection.
 */
class K8sServiceAccountCollection extends Collection {

  /**
   * The constructor.
   *
   * @param array $items
   *   The items.
   */
  public function __construct(array $items) {
    parent::__construct($this->getServiceAccountss($items));
  }

  /**
   * Get an array of service accounts.
   *
   * @param array $items
   *   The items.
   *
   * @return array
   *   The array of roles.
   */
  protected function getServiceAccountss(array $items) {
    foreach ($items ?: [] as &$item) {
      if ($item instanceof K8sServiceAccountModel) {
        continue;
      }

      $item = new K8sServiceAccountModel($item);
    }

    return $items;
  }

}
