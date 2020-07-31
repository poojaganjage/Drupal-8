<?php

namespace Drupal\k8s\Service\K8sClientExtension\Models;

use Maclof\Kubernetes\Models\Model;

/**
 * K8s metrics nodes model.
 */
class K8sMetricsNodeModel extends Model {

  /**
   * The api version.
   *
   * @var string
   */
  protected $apiVersion = 'metrics.k8s.io/v1beta1';

}
