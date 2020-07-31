<?php

namespace Drupal\aws_cloud\Service\S3;

/**
 * Interface S3ServiceInterface.
 */
interface S3ServiceInterface {

  /**
   * Set the cloud context.
   *
   * @param string $cloud_context
   *   Cloud context string.
   */
  public function setCloudContext($cloud_context);

  /**
   * Calls the Amazon S3 API endpoint GetObject.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getObject(array $params = []);

  /**
   * Calls the Amazon S3 API endpoint PutObject.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function putObject(array $params = []);

  /**
   * Calls the Amazon S3 API endpoint ListObjects.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function listObjects(array $params = []);

  /**
   * Calls the Amazon S3 API endpoint DeleteMatchingObjects.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deleteMatchingObjects(array $params = []);

}
