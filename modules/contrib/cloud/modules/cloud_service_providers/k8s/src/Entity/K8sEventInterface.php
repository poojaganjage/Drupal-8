<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a K8s Event entity.
 *
 * @ingroup k8s
 */
interface K8sEventInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getType();

  /**
   * {@inheritdoc}
   */
  public function setType($val);

  /**
   * {@inheritdoc}
   */
  public function getReason();

  /**
   * {@inheritdoc}
   */
  public function setReason($val);

  /**
   * {@inheritdoc}
   */
  public function getObjectKind();

  /**
   * {@inheritdoc}
   */
  public function setObjectKind($val);

  /**
   * {@inheritdoc}
   */
  public function getObjectName();

  /**
   * {@inheritdoc}
   */
  public function setObjectName($val);

  /**
   * {@inheritdoc}
   */
  public function getMessage();

  /**
   * {@inheritdoc}
   */
  public function setMessage($val);

  /**
   * {@inheritdoc}
   */
  public function getTimeStamp();

  /**
   * {@inheritdoc}
   */
  public function setTimeStamp($val);

}
