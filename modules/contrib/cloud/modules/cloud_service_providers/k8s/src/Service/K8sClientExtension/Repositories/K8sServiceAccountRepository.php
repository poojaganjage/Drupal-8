<?php

namespace Drupal\k8s\Service\K8sClientExtension\Repositories;

use Drupal\k8s\Service\K8sClientExtension\Collections\K8sServiceAccountCollection;
use Maclof\Kubernetes\Repositories\Repository;

/**
 * K8s service account repository.
 */
class K8sServiceAccountRepository extends Repository {

  /**
   * The uri.
   *
   * @var string
   */
  protected $uri = 'serviceaccounts';

  /**
   * {@inheritdoc}
   */
  protected function createCollection($response) {
    return new K8sServiceAccountCollection($response['items']);
  }

}
