<?php

namespace Drupal\k8s\Service\K8sClientExtension\Repositories;

use Drupal\k8s\Service\K8sClientExtension\Collections\K8sRoleBindingCollection;
use Maclof\Kubernetes\Repositories\Repository;

/**
 * K8s role binding respository.
 */
class K8sRoleBindingRepository extends Repository {

  /**
   * The uri.
   *
   * @var string
   */
  protected $uri = 'rolebindings';

  /**
   * {@inheritdoc}
   */
  protected function createCollection($response) {
    return new K8sRoleBindingCollection($response['items']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getApiVersion() {
    return 'rbac.authorization.k8s.io/v1';
  }

}
