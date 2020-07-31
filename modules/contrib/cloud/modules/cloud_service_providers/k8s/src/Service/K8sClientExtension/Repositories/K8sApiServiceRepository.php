<?php

namespace Drupal\k8s\Service\K8sClientExtension\Repositories;

use Drupal\k8s\Service\K8sClientExtension\Collections\K8sApiServiceCollection;
use Maclof\Kubernetes\Repositories\Repository;

/**
 * K8s cluster api service respository.
 */
class K8sApiServiceRepository extends Repository {

  /**
   * The uri.
   *
   * @var string
   */
  protected $uri = 'apiservices';

  /**
   * {@inheritdoc}
   */
  protected function createCollection($response) {
    return new K8sApiServiceCollection($response['items']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getApiVersion() {
    return 'apiregistration.k8s.io/v1';
  }

}
