<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Endpoint entity.
 *
 * @ingroup k8s
 */
interface K8sEndpointInterface extends ContentEntityInterface, EntityOwnerInterface {

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
  public function getNodeName();

  /**
   * {@inheritdoc}
   */
  public function setNodeName($node_name);

  /**
   * {@inheritdoc}
   */
  public function getAddresses();

  /**
   * {@inheritdoc}
   */
  public function setAddresses(array $addresses);

}
