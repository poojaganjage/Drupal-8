<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a ConfigMap entity.
 *
 * @ingroup k8s
 */
interface K8sConfigMapInterface extends ContentEntityInterface, EntityOwnerInterface, K8sExportableEntityInterface {

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
  public function getData();

  /**
   * {@inheritdoc}
   */
  public function setData($data);

}
