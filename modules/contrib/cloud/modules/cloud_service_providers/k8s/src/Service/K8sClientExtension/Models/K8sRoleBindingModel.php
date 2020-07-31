<?php

namespace Drupal\k8s\Service\K8sClientExtension\Models;

use Maclof\Kubernetes\Models\Model;

/**
 * K8s role bindings model.
 */
class K8sRoleBindingModel extends Model {

  /**
   * The api version.
   *
   * @var string
   */
  protected $apiVersion = 'rbac.authorization.k8s.io/v1';

}
