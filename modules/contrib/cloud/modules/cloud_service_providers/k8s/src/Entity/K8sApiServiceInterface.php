<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a API service entity.
 *
 * @ingroup k8s
 */
interface K8sApiServiceInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getGroupPriorityMinimum();

  /**
   * {@inheritdoc}
   */
  public function setGroupPriorityMinimum($group_priority_minimum);

  /**
   * {@inheritdoc}
   */
  public function getService();

  /**
   * {@inheritdoc}
   */
  public function setService($service);

  /**
   * {@inheritdoc}
   */
  public function getVersionPriority();

  /**
   * {@inheritdoc}
   */
  public function setVersionPriority($version_priority);

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
  public function getGroup();

  /**
   * {@inheritdoc}
   */
  public function setGroup($group);

  /**
   * {@inheritdoc}
   */
  public function getInsecureSkipTlsVerify();

  /**
   * {@inheritdoc}
   */
  public function setInsecureSkipTlsVerify($insecure_skip_tls_verify);

  /**
   * {@inheritdoc}
   */
  public function getVersion();

  /**
   * {@inheritdoc}
   */
  public function setVersion($version);

}
