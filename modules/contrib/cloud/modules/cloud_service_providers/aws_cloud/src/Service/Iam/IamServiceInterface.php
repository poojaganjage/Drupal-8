<?php

namespace Drupal\aws_cloud\Service\Iam;

/**
 * Interface IamServiceInterface.
 */
interface IamServiceInterface {

  /**
   * Set the cloud context.
   *
   * @param string $cloud_context
   *   Cloud context string.
   */
  public function setCloudContext($cloud_context);

  /**
   * Get instance profiles.
   *
   * @return array
   *   Instance profiles.
   */
  public function listInstanceProfiles();

}
