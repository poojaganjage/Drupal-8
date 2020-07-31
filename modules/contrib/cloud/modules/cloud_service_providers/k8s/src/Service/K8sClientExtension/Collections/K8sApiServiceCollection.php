<?php

namespace Drupal\k8s\Service\K8sClientExtension\Collections;

use Drupal\k8s\Service\K8sClientExtension\Models\K8sApiServiceModel;
use Maclof\Kubernetes\Collections\Collection;

/**
 * K8s api services collection.
 */
class K8sApiServiceCollection extends Collection {

  /**
   * The constructor.
   *
   * @param array $items
   *   The items.
   */
  public function __construct(array $items) {
    parent::__construct($this->getApiServices($items));
  }

  /**
   * Get an array of api services.
   *
   * @param array $items
   *   The items.
   *
   * @return array
   *   The array of roles.
   */
  protected function getApiServices(array $items) {
    foreach ($items ?: [] as &$item) {
      if ($item instanceof K8sApiServiceModel) {
        continue;
      }

      $item = new K8sApiServiceModel($item);
    }

    return $items;
  }

}
