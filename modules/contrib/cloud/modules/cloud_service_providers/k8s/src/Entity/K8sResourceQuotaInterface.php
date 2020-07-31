<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Resource Quota entity.
 *
 * @ingroup k8s
 */
interface K8sResourceQuotaInterface extends ContentEntityInterface, EntityOwnerInterface {

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
  public function getStatusHard();

  /**
   * {@inheritdoc}
   */
  public function setStatusHard($status_hard);

  /**
   * {@inheritdoc}
   */
  public function getStatusUsed();

  /**
   * {@inheritdoc}
   */
  public function setStatusUsed($status_used);

}
