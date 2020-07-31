<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Stateful Set entity.
 *
 * @ingroup k8s
 */
interface K8sStatefulSetInterface extends ContentEntityInterface, EntityOwnerInterface {

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
  public function getUpdateStrategy();

  /**
   * {@inheritdoc}
   */
  public function setUpdateStrategy($update_strategy);

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
  public function getServiceName();

  /**
   * {@inheritdoc}
   */
  public function setServiceName($service_name);

  /**
   * {@inheritdoc}
   */
  public function getPodManagementPolicy();

  /**
   * {@inheritdoc}
   */
  public function setPodManagementPolicy($pod_management_policy);

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
  public function getCurrentReplicas();

  /**
   * {@inheritdoc}
   */
  public function setCurrentReplicas($current_replicas);

  /**
   * {@inheritdoc}
   */
  public function getUpdatedReplicas();

  /**
   * {@inheritdoc}
   */
  public function setUpdatedReplicas($updated_replicas);

  /**
   * {@inheritdoc}
   */
  public function getCurrentRevision();

  /**
   * {@inheritdoc}
   */
  public function setCurrentRevision($current_revision);

  /**
   * {@inheritdoc}
   */
  public function getUpdateRevision();

  /**
   * {@inheritdoc}
   */
  public function setUpdateRevision($update_revision);

}
