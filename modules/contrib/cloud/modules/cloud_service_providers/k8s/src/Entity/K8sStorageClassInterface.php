<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Storage Class entity.
 *
 * @ingroup k8s
 */
interface K8sStorageClassInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getParameters();

  /**
   * {@inheritdoc}
   */
  public function setParameters($parameters);

  /**
   * {@inheritdoc}
   */
  public function getProvisioner();

  /**
   * {@inheritdoc}
   */
  public function setProvisioner($provisioner);

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
  public function getVolumeBindingMode();

  /**
   * {@inheritdoc}
   */
  public function setVolumeBindingMode($volume_binding_mode);

}
