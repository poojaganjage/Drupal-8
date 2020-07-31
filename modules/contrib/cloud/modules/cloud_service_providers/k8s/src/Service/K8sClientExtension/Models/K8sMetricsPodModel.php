<?php

namespace Drupal\k8s\Service\K8sClientExtension\Models;

use Maclof\Kubernetes\Models\Model;

/**
 * K8s metrics pods model.
 */
class K8sMetricsPodModel extends Model {

  /**
   * The api version.
   *
   * @var string
   */
  protected $apiVersion = 'metrics.k8s.io/v1beta1';

}
