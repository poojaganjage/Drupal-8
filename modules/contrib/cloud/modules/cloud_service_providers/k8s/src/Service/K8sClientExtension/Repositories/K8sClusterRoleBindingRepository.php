<?php

namespace Drupal\k8s\Service\K8sClientExtension\Repositories;

use Drupal\k8s\Service\K8sClientExtension\Collections\K8sClusterRoleBindingCollection;
use Maclof\Kubernetes\Repositories\Repository;

/**
 * K8s cluster role binding respository.
 */
class K8sClusterRoleBindingRepository extends Repository {

  /**
   * The uri.
   *
   * @var string
   */
  protected $uri = 'clusterrolebindings';

  /**
   * {@inheritdoc}
   */
  protected function createCollection($response) {
    return new K8sClusterRoleBindingCollection($response['items']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getApiVersion() {
    return 'rbac.authorization.k8s.io/v1';
  }

}
