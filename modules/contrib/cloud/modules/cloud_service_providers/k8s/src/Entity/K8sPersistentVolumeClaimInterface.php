<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Persistent Volume Claim entity.
 *
 * @ingroup k8s
 */
interface K8sPersistentVolumeClaimInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getNamespace();

  /**
   * {@inheritdoc}
   */
  public function setNamespace($val);

  /**
   * {@inheritdoc}
   */
  public function getPhase();

  /**
   * {@inheritdoc}
   */
  public function setPhase($val);

  /**
   * {@inheritdoc}
   */
  public function getVolumeName();

  /**
   * {@inheritdoc}
   */
  public function setVolumeName($val);

  /**
   * {@inheritdoc}
   */
  public function getCapacity();

  /**
   * {@inheritdoc}
   */
  public function setCapacity($val);

  /**
   * {@inheritdoc}
   */
  public function getRequest();

  /**
   * {@inheritdoc}
   */
  public function setRequest($val);

  /**
   * {@inheritdoc}
   */
  public function getAccessMode();

  /**
   * {@inheritdoc}
   */
  public function setAccessMode($val);

  /**
   * {@inheritdoc}
   */
  public function getStorageClass();

  /**
   * {@inheritdoc}
   */
  public function setStorageClass($val);

}
