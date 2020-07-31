<?php

namespace Drupal\k8s\Service\K8sClientExtension\Repositories;

use Drupal\k8s\Service\K8sClientExtension\Collections\K8sStatefulSetCollection;
use Maclof\Kubernetes\Repositories\Repository;

/**
 * K8s stateful set respository.
 */
class K8sStatefulSetRepository extends Repository {

  /**
   * The uri.
   *
   * @var string
   */
  protected $uri = 'statefulsets';

  /**
   * {@inheritdoc}
   */
  protected function createCollection($response) {
    return new K8sStatefulSetCollection($response['items']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getApiVersion() {
    return 'apps/v1';
  }

}
