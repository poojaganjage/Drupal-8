<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Cron Job entity.
 *
 * @ingroup k8s
 */
interface K8sCronJobInterface extends ContentEntityInterface, EntityOwnerInterface, K8sExportableEntityInterface {

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
  public function getSchedule();

  /**
   * {@inheritdoc}
   */
  public function setSchedule($schedule);

  /**
   * {@inheritdoc}
   */
  public function getActive();

  /**
   * {@inheritdoc}
   */
  public function setActive($active);

  /**
   * {@inheritdoc}
   */
  public function isSuspend();

  /**
   * {@inheritdoc}
   */
  public function setSuspend($suspend);

  /**
   * {@inheritdoc}
   */
  public function getLastScheduleTime();

  /**
   * {@inheritdoc}
   */
  public function setLastScheduleTime($last_schedule_time);

  /**
   * {@inheritdoc}
   */
  public function getConcurrencyPolicy();

  /**
   * {@inheritdoc}
   */
  public function setConcurrencyPolicy($concurrency_policy);

  /**
   * {@inheritdoc}
   */
  public function getStartingDeadlineSeconds();

  /**
   * {@inheritdoc}
   */
  public function setStartingDeadlineSeconds($starting_deadline_seconds);

}
