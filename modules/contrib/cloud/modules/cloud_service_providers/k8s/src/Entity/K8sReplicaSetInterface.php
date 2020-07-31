<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Replica Set entity.
 *
 * @ingroup k8s
 */
interface K8sReplicaSetInterface extends ContentEntityInterface, EntityOwnerInterface {

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
  public function getReplicas();

  /**
   * {@inheritdoc}
   */
  public function setReplicas($replicas);

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
  public function getTemplate();

  /**
   * {@inheritdoc}
   */
  public function setTemplate($template);

  /**
   * {@inheritdoc}
   */
  public function getConditions();

  /**
   * {@inheritdoc}
   */
  public function setConditions($conditions);

  /**
   * {@inheritdoc}
   */
  public function getAvailableReplicas();

  /**
   * {@inheritdoc}
   */
  public function setAvailableReplicas($available_replicas);

  /**
   * {@inheritdoc}
   */
  public function getFullyLabeledReplicas();

  /**
   * {@inheritdoc}
   */
  public function setFullyLabeledReplicas($fully_labeled_replicas);

  /**
   * {@inheritdoc}
   */
  public function getReadyReplicas();

  /**
   * {@inheritdoc}
   */
  public function setReadyReplicas($ready_replicas);

  /**
   * {@inheritdoc}
   */
  public function getObservedGeneration();

  /**
   * {@inheritdoc}
   */
  public function setObservedGeneration($observed_generation);

}
