<?php

namespace Drupal\k8s\Service\K8sClientExtension\Repositories;

use Drupal\k8s\Service\K8sClientExtension\Collections\K8sMetricsNodeCollection;
use Maclof\Kubernetes\Repositories\Repository;

/**
 * K8s node repository for metrics.
 */
class K8sMetricsNodeRepository extends Repository {

  /**
   * The uri.
   *
   * @var string
   */
  protected $uri = 'nodes';

  /**
   * Whether use namespace or not.
   *
   * @var bool
   */
  protected $namespace = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function createCollection($response) {
    return new K8sMetricsNodeCollection($response['items']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getApiVersion() {
    return 'metrics.k8s.io/v1beta1';
  }

}
