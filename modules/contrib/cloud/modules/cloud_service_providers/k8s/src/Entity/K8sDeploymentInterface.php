<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Deployment entity.
 *
 * @ingroup k8s
 */
interface K8sDeploymentInterface extends ContentEntityInterface, EntityOwnerInterface, K8sExportableEntityInterface {

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
  public function getStrategy();

  /**
   * {@inheritdoc}
   */
  public function setStrategy($strategy);

  /**
   * {@inheritdoc}
   */
  public function getMinReadySeconds();

  /**
   * {@inheritdoc}
   */
  public function setMinReadySeconds($min_ready_seconds);

  /**
   * {@inheritdoc}
   */
  public function getRevisionHistoryLimit();

  /**
   * {@inheritdoc}
   */
  public function setRevisionHistoryLimit($revision_history_limit);

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
  public function getCollisionCount();

  /**
   * {@inheritdoc}
   */
  public function setCollisionCount($collision_count);

  /**
   * {@inheritdoc}
   */
  public function getObservedGeneration();

  /**
   * {@inheritdoc}
   */
  public function setObservedGeneration($observed_generation);

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
  public function getReplicas();

  /**
   * {@inheritdoc}
   */
  public function setReplicas($replicas);

  /**
   * {@inheritdoc}
   */
  public function getUnavailableReplicas();

  /**
   * {@inheritdoc}
   */
  public function setUnavailableReplicas($unavailable_replicas);

  /**
   * {@inheritdoc}
   */
  public function getUpdatedReplicas();

  /**
   * {@inheritdoc}
   */
  public function setUpdatedReplicas($updated_replicas);

}
