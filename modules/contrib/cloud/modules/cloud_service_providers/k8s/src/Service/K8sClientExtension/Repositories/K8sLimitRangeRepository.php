<?php

namespace Drupal\k8s\Service\K8sClientExtension\Repositories;

use Drupal\k8s\Service\K8sClientExtension\Collections\K8sLimitRangeCollection;
use Maclof\Kubernetes\Repositories\Repository;

/**
 * K8s limit range repository.
 */
class K8sLimitRangeRepository extends Repository {

  /**
   * The uri.
   *
   * @var string
   */
  protected $uri = 'limitranges';

  /**
   * {@inheritdoc}
   */
  protected function createCollection($response) {
    return new K8sLimitRangeCollection($response['items']);
  }

}
