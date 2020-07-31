<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a persistent volume entity.
 *
 * @ingroup k8s
 */
interface K8sPersistentVolumeInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getCapacity();

  /**
   * {@inheritdoc}
   */
  public function setCapacity($capacity);

  /**
   * {@inheritdoc}
   */
  public function getAccessModes();

  /**
   * {@inheritdoc}
   */
  public function setAccessModes($access_modes);

  /**
   * {@inheritdoc}
   */
  public function getReclaimPolicy();

  /**
   * {@inheritdoc}
   */
  public function setReclaimPolicy($reclaim_policy);

  /**
   * {@inheritdoc}
   */
  public function getStorageClassName();

  /**
   * {@inheritdoc}
   */
  public function setStorageClassName($storage_class_name);

  /**
   * {@inheritdoc}
   */
  public function getPhase();

  /**
   * {@inheritdoc}
   */
  public function setPhase($phase);

  /**
   * {@inheritdoc}
   */
  public function getClaimRef();

  /**
   * {@inheritdoc}
   */
  public function setClaimRef($claim_ref);

  /**
   * {@inheritdoc}
   */
  public function getReason();

  /**
   * {@inheritdoc}
   */
  public function setReason($reason);

}
