<?php

namespace Drupal\cloud\Controller;

use Drupal\cloud\Entity\CloudProjectInterface;

/**
 * Common interfaces for the CloudProjectControllerInterface.
 */
interface CloudProjectControllerInterface {

  /**
   * Launch Operation.
   *
   * @param \Drupal\cloud\Entity\CloudProjectInterface $cloud_project
   *   The instance of CloudProjectInterface.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function launch(CloudProjectInterface $cloud_project);

}
