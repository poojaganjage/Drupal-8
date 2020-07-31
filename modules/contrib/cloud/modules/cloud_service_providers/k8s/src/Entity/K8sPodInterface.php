<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Pod entity.
 *
 * @ingroup k8s
 */
interface K8sPodInterface extends ContentEntityInterface, EntityOwnerInterface, K8sExportableEntityInterface {

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
  public function getStatus();

  /**
   * {@inheritdoc}
   */
  public function setStatus($status);

  /**
   * {@inheritdoc}
   */
  public function getQosClass();

  /**
   * {@inheritdoc}
   */
  public function setQosClass($qos_class);

  /**
   * {@inheritdoc}
   */
  public function getNodeName();

  /**
   * {@inheritdoc}
   */
  public function setNodeName($node_name);

  /**
   * {@inheritdoc}
   */
  public function getPodIp();

  /**
   * {@inheritdoc}
   */
  public function setPodIp($pod_ip);

  /**
   * {@inheritdoc}
   */
  public function getContainers();

  /**
   * {@inheritdoc}
   */
  public function setContainers($containers);

  /**
   * {@inheritdoc}
   */
  public function getRestarts();

  /**
   * {@inheritdoc}
   */
  public function setRestarts($restarts);

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
  public function getCpuUsage();

  /**
   * {@inheritdoc}
   */
  public function setCpuUsage($cpu_usage);

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

  /**
   * {@inheritdoc}
   */
  public function getMemoryUsage();

  /**
   * {@inheritdoc}
   */
  public function setMemoryUsage($memory_usage);

}
