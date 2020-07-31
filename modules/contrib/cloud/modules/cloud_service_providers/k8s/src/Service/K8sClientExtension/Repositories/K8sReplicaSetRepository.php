<?php

namespace Drupal\k8s\Service\K8sClientExtension\Repositories;

use Drupal\k8s\Service\K8sClientExtension\Collections\K8sReplicaSetCollection;
use Maclof\Kubernetes\Repositories\Repository;

/**
 * K8s replica set respository.
 */
class K8sReplicaSetRepository extends Repository {

  /**
   * The uri.
   *
   * @var string
   */
  protected $uri = 'replicasets';

  /**
   * {@inheritdoc}
   */
  protected function createCollection($response) {
    return new K8sReplicaSetCollection($response['items']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getApiVersion() {
    return 'apps/v1';
  }

}
