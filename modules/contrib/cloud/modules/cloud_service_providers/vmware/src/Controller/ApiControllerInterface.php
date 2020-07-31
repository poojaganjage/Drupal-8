<?php

namespace Drupal\vmware\Controller;

/**
 * {@inheritdoc}
 */
interface ApiControllerInterface {

  /**
   * Update all VMs.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateVmList($cloud_context);

}
