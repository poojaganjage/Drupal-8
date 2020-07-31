<?php

namespace Drupal\aws_cloud\Controller\Ec2;

use Drupal\aws_cloud\Entity\Ec2\InstanceInterface;

/**
 * {@inheritdoc}
 */
interface ApiControllerInterface {

  /**
   * Update all instances in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateInstanceList($cloud_context);

  /**
   * Update all images in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateImageList($cloud_context);

  /**
   * Update all security groups in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateSecurityGroupList($cloud_context);

  /**
   * Update all network interfaces in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateNetworkInterfaceList($cloud_context);

  /**
   * Update all Elastic IPs in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateElasticIpList($cloud_context);

  /**
   * Update all key pairs in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateKeyPairList($cloud_context);

  /**
   * Update all volumes in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateVolumeList($cloud_context);

  /**
   * Update all snapshots in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateSnapshotList($cloud_context);

  /**
   * Update all entities in a given region.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateAll();

  /**
   * Search images.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response of images.
   */
  public function searchImages($cloud_context);

  /**
   * Get instance metrics.
   *
   * @param string $cloud_context
   *   Cloud context string.
   * @param \Drupal\aws_cloud\Entity\Ec2\InstanceInterface $aws_cloud_instance
   *   Cloud context string.
   *
   * @return Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response of an instance's metrics.
   */
  public function getInstanceMetrics($cloud_context, InstanceInterface $aws_cloud_instance);

}
