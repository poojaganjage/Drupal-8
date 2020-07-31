<?php

namespace Drupal\cloud_budget\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a cloud cost storage entity.
 *
 * @ingroup cloud_budget
 */
interface CloudCostStorageInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getCloudContext();

  /**
   * {@inheritdoc}
   */
  public function getPayer();

  /**
   * {@inheritdoc}
   */
  public function setPayer($payer);

  /**
   * {@inheritdoc}
   */
  public function getCost();

  /**
   * {@inheritdoc}
   */
  public function setCost($cost);

  /**
   * {@inheritdoc}
   */
  public function getResources();

  /**
   * {@inheritdoc}
   */
  public function setResources($resources);

  /**
   * {@inheritdoc}
   */
  public function setCreated();

  /**
   * {@inheritdoc}
   */
  public function setChanged($changed);

  /**
   * {@inheritdoc}
   */
  public function getChanged();

  /**
   * {@inheritdoc}
   */
  public function getRefreshed();

  /**
   * {@inheritdoc}
   */
  public function setRefreshed($time);

}
