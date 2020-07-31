<?php

namespace Drupal\aws_cloud\Controller\Vpc;

/**
 * {@inheritdoc}
 */
interface ApiControllerInterface {

  /**
   * Update all VPCs in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateVpcList($cloud_context);

  /**
   * Update all VPC Peering Connections in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateVpcPeeringConnectionList($cloud_context);

  /**
   * Update all subnets in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateSubnetList($cloud_context);

}
