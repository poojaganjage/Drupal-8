<?php

namespace Drupal\k8s\Service\K8sClientExtension\Repositories;

use Drupal\k8s\Service\K8sClientExtension\Collections\K8sMetricsPodCollection;
use Maclof\Kubernetes\Repositories\Repository;

/**
 * K8s pod repository for metrics.
 */
class K8sMetricsPodRepository extends Repository {

  /**
   * The uri.
   *
   * @var string
   */
  protected $uri = 'pods';

  /**
   * {@inheritdoc}
   */
  protected function createCollection($response) {
    return new K8sMetricsPodCollection($response['items']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getApiVersion() {
    return 'metrics.k8s.io/v1beta1';
  }

}
