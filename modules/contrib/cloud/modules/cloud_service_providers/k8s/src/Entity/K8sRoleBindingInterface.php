<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Role Binding entity.
 *
 * @ingroup k8s
 */
interface K8sRoleBindingInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getNamespace();

  /**
   * {@inheritdoc}
   */
  public function setNamespace($namespace);

  /**
   * {@inheritdoc}
   */
  public function getRole();

  /**
   * {@inheritdoc}
   */
  public function setRole($role_ref);

  /**
   * {@inheritdoc}
   */
  public function getSubjects();

  /**
   * {@inheritdoc}
   */
  public function setSubjects($subjects);

}
