<?php

namespace Drupal\aws_cloud\Controller\Ec2;

/**
 * Provides an interface showing price list.
 */
interface InstanceTypePriceControllerInterface {

  /**
   * Show instance type prices list.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function show($cloud_context);

  /**
   * Update instance type prices list.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function update($cloud_context);

}
