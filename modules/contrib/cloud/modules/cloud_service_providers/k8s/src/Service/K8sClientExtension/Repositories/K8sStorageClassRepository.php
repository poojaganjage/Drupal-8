<?php

namespace Drupal\k8s\Service\K8sClientExtension\Repositories;

use Drupal\k8s\Service\K8sClientExtension\Collections\K8sStorageClassCollection;
use Maclof\Kubernetes\Repositories\Repository;

/**
 * K8s storage class respository.
 */
class K8sStorageClassRepository extends Repository {

  /**
   * The uri.
   *
   * @var string
   */
  protected $uri = 'storageclasses';

  /**
   * {@inheritdoc}
   */
  protected function createCollection($response) {
    return new K8sStorageClassCollection($response['items']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getApiVersion() {
    return 'storage.k8s.io/v1';
  }

}
