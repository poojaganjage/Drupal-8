<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Cluster Role Binding entity.
 *
 * @ingroup k8s
 */
interface K8sClusterRoleBindingInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getSubjects();

  /**
   * {@inheritdoc}
   */
  public function setSubjects($subjects);

  /**
   * {@inheritdoc}
   */
  public function getRoleRef();

  /**
   * {@inheritdoc}
   */
  public function setRoleRef($role_ref);

}
