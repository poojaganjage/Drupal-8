<?php

namespace Drupal\k8s\Service\K8sClientExtension\Models;

use Maclof\Kubernetes\Models\Model;

/**
 * K8s priority classes model.
 */
class K8sPriorityClassModel extends Model {

  /**
   * The api version.
   *
   * @var string
   */
  protected $apiVersion = 'scheduling.k8s.io/v1';

}
