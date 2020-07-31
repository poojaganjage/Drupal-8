<?php

namespace Drupal\k8s\Service\K8sClientExtension\Repositories;

use Drupal\k8s\Service\K8sClientExtension\Collections\K8sClusterRoleCollection;
use Maclof\Kubernetes\Repositories\Repository;

/**
 * K8s cluster role respository.
 */
class K8sClusterRoleRepository extends Repository {

  /**
   * The uri.
   *
   * @var string
   */
  protected $uri = 'clusterroles';

  /**
   * {@inheritdoc}
   */
  protected function createCollection($response) {
    return new K8sClusterRoleCollection($response['items']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getApiVersion() {
    return 'rbac.authorization.k8s.io/v1';
  }

}
