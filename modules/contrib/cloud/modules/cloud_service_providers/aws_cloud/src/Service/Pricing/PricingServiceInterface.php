<?php

namespace Drupal\aws_cloud\Service\Pricing;

use Drupal\cloud\Entity\CloudConfig;

/**
 * Interface PricingServiceInterface.
 */
interface PricingServiceInterface {

  /**
   * Set the cloud context.
   *
   * @param string $cloud_context
   *   Cloud context string.
   */
  public function setCloudContext($cloud_context);

  /**
   * Set the cloud service provider (CloudConfig) entity.
   *
   * @param \Drupal\cloud\Entity\CloudConfig $cloud_config_entity
   *   The cloud service provider (CloudConfig) entity.
   */
  public function setCloudConfigEntity(CloudConfig $cloud_config_entity);

  /**
   * Get instance types from the EC2 pricing endpoint.
   *
   * @return array
   *   Instance type array.
   */
  public function getInstanceTypes();

}
