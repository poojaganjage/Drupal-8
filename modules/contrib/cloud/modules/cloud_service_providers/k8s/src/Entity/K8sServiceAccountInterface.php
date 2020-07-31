<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Service Account entity.
 *
 * @ingroup k8s
 */
interface K8sServiceAccountInterface extends ContentEntityInterface, EntityOwnerInterface {

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
  public function getSecrets();

  /**
   * {@inheritdoc}
   */
  public function setSecrets($secrets);

  /**
   * {@inheritdoc}
   */
  public function getImagePullSecrets();

  /**
   * {@inheritdoc}
   */
  public function setImagePullSecrets($image_pull_secrets);

}
