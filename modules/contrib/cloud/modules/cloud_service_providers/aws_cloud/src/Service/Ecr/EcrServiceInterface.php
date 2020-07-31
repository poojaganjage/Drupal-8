<?php

namespace Drupal\aws_cloud\Service\Ecr;

/**
 * Interface EcrServiceInterface.
 */
interface EcrServiceInterface {

  /**
   * Set the cloud context.
   *
   * @param string $cloud_context
   *   Cloud context string.
   */
  public function setCloudContext($cloud_context);

  /**
   * Get authorization token.
   *
   * @param array $params
   *   Parameter array.
   *
   * @return array
   *   Authorization token array.
   */
  public function getAuthorizationToken(array $params);

  /**
   * Creates an image repository.
   *
   * @param string $name
   *   The repository to create, including the namespace.
   *
   * @return array
   *   Results.
   */
  public function createRepository($name);

  /**
   * Describe ECR repositories.
   *
   * @param array $params
   *   Parameter array.
   *
   * @return array
   *   Repository array.
   */
  public function describeRepositories(array $params);

  /**
   * Build the ECR endpoint using cloud_config parameters.
   *
   * @return string
   *   The ECR endpoint.
   */
  public function getEcrEndpoint();

  /**
   * Check whether repository exists.
   *
   * @param string $name
   *   Repository name to check.
   *
   * @return bool
   *   True or false depending if the repository exists.
   */
  public function doesRepositoryExists($name);

  /**
   * Describe images.
   *
   * @param array $params
   *   Parameter array.
   *
   * @return array
   *   Repository array.
   */
  public function describeImages(array $params);

  /**
   * Check if an image exists.
   *
   * @param string $name
   *   Repository name.
   * @param string $tag
   *   Repository tag.
   *
   * @return bool
   *   True if image exists else false.
   */
  public function doesImageExist($name, $tag);

}
