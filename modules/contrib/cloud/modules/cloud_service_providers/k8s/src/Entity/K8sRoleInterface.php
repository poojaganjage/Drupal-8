<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Role entity.
 *
 * @ingroup k8s
 */
interface K8sRoleInterface extends ContentEntityInterface, EntityOwnerInterface {

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
  public function getRules();

  /**
   * {@inheritdoc}
   */
  public function setRules($rules);

}
