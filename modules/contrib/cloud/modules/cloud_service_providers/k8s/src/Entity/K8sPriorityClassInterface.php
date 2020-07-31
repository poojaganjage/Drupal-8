<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Priority Class entity.
 *
 * @ingroup k8s
 */
interface K8sPriorityClassInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getValue();

  /**
   * {@inheritdoc}
   */
  public function setValue($value);

  /**
   * {@inheritdoc}
   */
  public function getGlobalDefault();

  /**
   * {@inheritdoc}
   */
  public function setGlobalDefault($global_default);

  /**
   * {@inheritdoc}
   */
  public function getDescription();

  /**
   * {@inheritdoc}
   */
  public function setDescription($description);

}
