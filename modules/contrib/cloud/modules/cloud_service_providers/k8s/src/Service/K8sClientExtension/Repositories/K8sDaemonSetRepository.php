<?php

namespace Drupal\k8s\Service\K8sClientExtension\Repositories;

use Drupal\k8s\Service\K8sClientExtension\Collections\K8sDaemonSetCollection;
use Maclof\Kubernetes\Repositories\Repository;

/**
 * K8s daemon set respository.
 */
class K8sDaemonSetRepository extends Repository {

  /**
   * The uri.
   *
   * @var string
   */
  protected $uri = 'daemonsets';

  /**
   * {@inheritdoc}
   */
  protected function createCollection($response) {
    return new K8sDaemonSetCollection($response['items']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getApiVersion() {
    return 'apps/v1';
  }

}
