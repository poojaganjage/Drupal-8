<?php

namespace Drupal\cloud_budget\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a cloud credit entity.
 *
 * @ingroup cloud_budget
 */
interface CloudCreditInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getCloudContext();

  /**
   * {@inheritdoc}
   */
  public function getName();

  /**
   * {@inheritdoc}
   */
  public function setName($name);

  /**
   * {@inheritdoc}
   */
  public function getUser();

  /**
   * {@inheritdoc}
   */
  public function setUser($user);

  /**
   * {@inheritdoc}
   */
  public function getAmount();

  /**
   * {@inheritdoc}
   */
  public function setAmount($amount);

  /**
   * {@inheritdoc}
   */
  public function created();

  /**
   * {@inheritdoc}
   */
  public function changed();

  /**
   * {@inheritdoc}
   */
  public function getRefreshed();

  /**
   * {@inheritdoc}
   */
  public function setRefreshed($time);

}
