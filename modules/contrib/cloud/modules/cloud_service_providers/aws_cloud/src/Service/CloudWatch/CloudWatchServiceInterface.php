<?php

namespace Drupal\aws_cloud\Service\CloudWatch;

/**
 * Interface CloudWatchServiceInterface.
 */
interface CloudWatchServiceInterface {

  /**
   * Set the cloud context.
   *
   * @param string $cloud_context
   *   Cloud context string.
   */
  public function setCloudContext($cloud_context);

  /**
   * Calls the Amazon CloudWatch API endpoint getMetricData.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getMetricData(array $params = []);

}
