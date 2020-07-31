<?php

namespace Drupal\k8s\Service\K8sClientExtension\Repositories;

use Drupal\k8s\Service\K8sClientExtension\Collections\K8sPriorityClassCollection;
use Maclof\Kubernetes\Repositories\Repository;

/**
 * K8s cluster priority class repository.
 */
class K8sPriorityClassRepository extends Repository {

  /**
   * The uri.
   *
   * @var string
   */
  protected $uri = 'priorityclasses';

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
    return new K8sPriorityClassCollection($response['items']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getApiVersion() {
    // @FIXME refactor to avoid duplicate apiVersion def.
    return 'scheduling.k8s.io/v1';
  }

}
