<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a K8s Namespace entity.
 *
 * @ingroup k8s
 */
interface K8sNamespaceInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getStatus();

  /**
   * {@inheritdoc}
   */
  public function setStatus($status);

}
