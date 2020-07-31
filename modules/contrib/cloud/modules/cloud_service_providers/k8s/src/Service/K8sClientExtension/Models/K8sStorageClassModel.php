<?php

namespace Drupal\k8s\Service\K8sClientExtension\Models;

use Maclof\Kubernetes\Models\Model;

/**
 * K8s storage classes model.
 */
class K8sStorageClassModel extends Model {

  /**
   * The api version.
   *
   * @var string
   */
  protected $apiVersion = 'storage.k8s.io/v1';

}
