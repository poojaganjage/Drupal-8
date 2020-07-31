<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a K8s Node entity.
 *
 * @ingroup k8s
 */
interface K8sNodeInterface extends ContentEntityInterface, EntityOwnerInterface {

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
  public function getAddresses();

  /**
   * {@inheritdoc}
   */
  public function setAddresses(array $addresses);

  /**
   * {@inheritdoc}
   */
  public function getPodCidr();

  /**
   * {@inheritdoc}
   */
  public function setPodCidr($pod_cidr);

  /**
   * {@inheritdoc}
   */
  public function getProviderId();

  /**
   * {@inheritdoc}
   */
  public function setProviderId($provider_id);

  /**
   * {@inheritdoc}
   */
  public function isUnschedulable();

  /**
   * {@inheritdoc}
   */
  public function setUnschedulable($unschedulable);

  /**
   * {@inheritdoc}
   */
  public function getMachineId();

  /**
   * {@inheritdoc}
   */
  public function setMachineId($machine_id);

  /**
   * {@inheritdoc}
   */
  public function getSystemUuid();

  /**
   * {@inheritdoc}
   */
  public function setSystemUuid($system_uuid);

  /**
   * {@inheritdoc}
   */
  public function getBootId();

  /**
   * {@inheritdoc}
   */
  public function setBootId($boot_id);

  /**
   * {@inheritdoc}
   */
  public function getKernelVersion();

  /**
   * {@inheritdoc}
   */
  public function setKernelVersion($kernel_version);

  /**
   * {@inheritdoc}
   */
  public function getOsImage();

  /**
   * {@inheritdoc}
   */
  public function setOsImage($os_image);

  /**
   * {@inheritdoc}
   */
  public function getContainerRuntimeVersion();

  /**
   * {@inheritdoc}
   */
  public function setContainerRuntimeVersion($container_runtime_version);

  /**
   * {@inheritdoc}
   */
  public function getKubeletVersion();

  /**
   * {@inheritdoc}
   */
  public function setKubeletVersion($kubelet_version);

  /**
   * {@inheritdoc}
   */
  public function getKubeProxyVersion();

  /**
   * {@inheritdoc}
   */
  public function setKubeProxyVersion($kube_proxy_version);

  /**
   * {@inheritdoc}
   */
  public function getOperatingSystem();

  /**
   * {@inheritdoc}
   */
  public function setOperatingSystem($operating_system);

  /**
   * {@inheritdoc}
   */
  public function getArchitecture();

  /**
   * {@inheritdoc}
   */
  public function setArchitecture($architecture);

  /**
   * {@inheritdoc}
   */
  public function getCpuCapacity();

  /**
   * {@inheritdoc}
   */
  public function setCpuCapacity($cpu_capacity);

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
  public function getMemoryCapacity();

  /**
   * {@inheritdoc}
   */
  public function setMemoryCapacity($memory_capacity);

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

  /**
   * {@inheritdoc}
   */
  public function getPodsCapacity();

  /**
   * {@inheritdoc}
   */
  public function setPodsCapacity($pods_capacity);

  /**
   * {@inheritdoc}
   */
  public function getPodsAllocation();

  /**
   * {@inheritdoc}
   */
  public function setPodsAllocation($pods_allocation);

}
