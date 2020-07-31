<?php

namespace Drupal\vmware\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a VMware entity.
 *
 * @ingroup vmware
 */
interface VmwareEntityInterface extends ContentEntityInterface, EntityOwnerInterface {

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
