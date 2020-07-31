<?php

namespace Drupal\vmware\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a VMware VM entity.
 *
 * @ingroup vmware
 */
interface VmwareVmInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getVmId();

  /**
   * {@inheritdoc}
   */
  public function setVmId($vm_id);

  /**
   * {@inheritdoc}
   */
  public function getPowerState();

  /**
   * {@inheritdoc}
   */
  public function setPowerState($power_state);

  /**
   * {@inheritdoc}
   */
  public function getCpuCount();

  /**
   * {@inheritdoc}
   */
  public function setCpuCount($cpu_count);

  /**
   * {@inheritdoc}
   */
  public function getMemorySize();

  /**
   * {@inheritdoc}
   */
  public function setMemorySize($memory_size);

}
