<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Cluster Role entity.
 *
 * @ingroup k8s
 */
interface K8sClusterRoleInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getRules();

  /**
   * {@inheritdoc}
   */
  public function setRules($rules);

}
