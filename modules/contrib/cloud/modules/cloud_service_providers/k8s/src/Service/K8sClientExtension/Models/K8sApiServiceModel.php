<?php

namespace Drupal\k8s\Service\K8sClientExtension\Models;

use Maclof\Kubernetes\Models\Model;

/**
 * K8s api services model.
 */
class K8sApiServiceModel extends Model {

  /**
   * The api version.
   *
   * @var string
   */
  protected $apiVersion = 'apiregistration.k8s.io/v1';

}
