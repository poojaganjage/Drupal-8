<?php

namespace Drupal\k8s\Service\K8sClientExtension\Repositories;

use Drupal\k8s\Service\K8sClientExtension\Collections\K8sRoleCollection;
use Maclof\Kubernetes\Repositories\Repository;

/**
 * K8s role respository.
 */
class K8sRoleRepository extends Repository {

  /**
   * The uri.
   *
   * @var string
   */
  protected $uri = 'roles';

  /**
   * {@inheritdoc}
   */
  protected function createCollection($response) {
    return new K8sRoleCollection($response['items']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getApiVersion() {
    return 'rbac.authorization.k8s.io/v1';
  }

}
