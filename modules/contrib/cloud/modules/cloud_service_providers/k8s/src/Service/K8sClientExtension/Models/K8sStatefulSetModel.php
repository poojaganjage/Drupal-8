<?php

namespace Drupal\k8s\Service\K8sClientExtension\Models;

use Maclof\Kubernetes\Models\Model;

/**
 * K8s stateful sets model.
 */
class K8sStatefulSetModel extends Model {

  /**
   * The api version.
   *
   * @var string
   */
  protected $apiVersion = 'apps/v1';

}
