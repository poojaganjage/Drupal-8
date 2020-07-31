<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Network Policy entity.
 *
 * @ingroup k8s
 */
interface K8sNetworkPolicyInterface extends ContentEntityInterface, EntityOwnerInterface, K8sExportableEntityInterface {

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
  public function getStatus();

  /**
   * {@inheritdoc}
   */
  public function setStatus($status);

  /**
   * {@inheritdoc}
   */
  public function getEgress();

  /**
   * {@inheritdoc}
   */
  public function setEgress($egress);

  /**
   * {@inheritdoc}
   */
  public function getIngress();

  /**
   * {@inheritdoc}
   */
  public function setIngress($ingress);

  /**
   * {@inheritdoc}
   */
  public function getPolicyTypes();

  /**
   * {@inheritdoc}
   */
  public function setPolicyTypes($policy_types);

  /**
   * {@inheritdoc}
   */
  public function getPodSelector();

  /**
   * {@inheritdoc}
   */
  public function setPodSelector($pod_selector);

}
