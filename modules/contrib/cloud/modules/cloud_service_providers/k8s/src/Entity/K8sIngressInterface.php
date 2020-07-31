<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Ingress entity.
 *
 * @ingroup k8s
 */
interface K8sIngressInterface extends ContentEntityInterface, EntityOwnerInterface {

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
  public function getBackend();

  /**
   * {@inheritdoc}
   */
  public function setBackend($backend);

  /**
   * {@inheritdoc}
   */
  public function getRules();

  /**
   * {@inheritdoc}
   */
  public function setRules($rules);

  /**
   * {@inheritdoc}
   */
  public function getTls();

  /**
   * {@inheritdoc}
   */
  public function setTls($tls);

  /**
   * {@inheritdoc}
   */
  public function getLoadBalancer();

  /**
   * {@inheritdoc}
   */
  public function setLoadBalancer($load_balancer);

}
