<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Daemon Set entity.
 *
 * @ingroup k8s
 */
interface K8sDaemonSetInterface extends ContentEntityInterface, EntityOwnerInterface {

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
  public function getCpuRequest();

  /**
   * {@inheritdoc}
   */
  public function setCpuRequest($cpu_request);

  /**
   * {@inheritdoc}
   */
  public function getCpuLimit();

  /**
   * {@inheritdoc}
   */
  public function setCpuLimit($cpu_limit);

  /**
   * {@inheritdoc}
   */
  public function getMemoryRequest();

  /**
   * {@inheritdoc}
   */
  public function setMemoryRequest($memory_request);

  /**
   * {@inheritdoc}
   */
  public function getMemoryLimit();

  /**
   * {@inheritdoc}
   */
  public function setMemoryLimit($memory_limit);

}
