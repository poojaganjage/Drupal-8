<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Service entity.
 *
 * @ingroup k8s
 */
interface K8sServiceEntityInterface extends ContentEntityInterface, EntityOwnerInterface {

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
  public function getSelector();

  /**
   * {@inheritdoc}
   */
  public function setSelector($selector);

  /**
   * {@inheritdoc}
   */
  public function getSessionAffinity();

  /**
   * {@inheritdoc}
   */
  public function setSessionAffinity($session_affinity);

  /**
   * {@inheritdoc}
   */
  public function getClusterIp();

  /**
   * {@inheritdoc}
   */
  public function setClusterIp($cluster_ip);

  /**
   * {@inheritdoc}
   */
  public function getInternalEndpoints();

  /**
   * {@inheritdoc}
   */
  public function setInternalEndpoints($internal_endpoints);

  /**
   * {@inheritdoc}
   */
  public function getExternalEndpoints();

  /**
   * {@inheritdoc}
   */
  public function setExternalEndpoints($external_endpoints);

}
