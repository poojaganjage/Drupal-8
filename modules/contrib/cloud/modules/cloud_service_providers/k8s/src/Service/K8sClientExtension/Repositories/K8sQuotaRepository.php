<?php

namespace Drupal\k8s\Service\K8sClientExtension\Repositories;

use Maclof\Kubernetes\Repositories\QuotaRepository;

/**
 * K8s quota repository.
 */
class K8sQuotaRepository extends QuotaRepository {

  /**
   * Whether use namespace or not.
   *
   * @var bool
   */
  protected $namespace = TRUE;

}
