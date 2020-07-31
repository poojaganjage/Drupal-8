<?php

namespace Drupal\docker\Service;

/**
 * Docker service interface.
 */
interface DockerServiceInterface {

  /**
   * Pull image into Docker.
   *
   * @param string $from_image
   *   Can be a name or Uri.
   *
   * @return string
   *   Response body.
   */
  public function pullImage($from_image);

  /**
   * Push an image.
   *
   * @param string $name
   *   Image name to push.
   * @param array $auth_array
   *   Authentication array according to
   *   https://docs.docker.com/engine/api/v1.39/#section/Authentication.
   *
   * @return string
   *   Response body.
   */
  public function pushImage($name, array $auth_array = []);

  /**
   * Inspect an image.
   *
   * @param string $name
   *   Name of image to inspect.
   *
   * @return array
   *   Array containing image information.
   */
  public function inspectImage($name);

  /**
   * Tag an image.
   *
   * @param string $name
   *   The image to tag.
   * @param string $repo
   *   Repository to tag in.
   * @param string $tag
   *   Name of new tag.
   */
  public function tagImage($name, $repo, $tag);

  /**
   * List images in docker.
   *
   * @return array
   *   Array of images.
   */
  public function listImages();

  /**
   * Set the return format.
   *
   * @param string $format
   *   Return format.
   */
  public function setFormat($format);

  /**
   * Set a boolean on whether to use the local docker unix socket.
   *
   * @param bool $use_socket
   *   TRUE | FALSE.
   */
  public function setUseSocket($use_socket);

  /**
   * Set api version.
   *
   * @param string $api_version
   *   The api version.
   */
  public function setApiVersion($api_version);

  /**
   * Set the unix socket path.
   *
   * @param string $unix_socket
   *   The unix socket string.
   */
  public function setUnixSocket($unix_socket);

  /**
   * Parse and extract information from an image string.
   *
   * @param string $image
   *   The image string to parse.
   *
   * @return array
   *   An array with image information.
   */
  public function parseImage($image);

  /**
   * Check if docker is available.
   *
   * @param string $unix_socket
   *   Docker unix socket to check.
   * @param string $api_version
   *   Api version to check.
   *
   * @return bool
   *   TRUE if docker is up.
   */
  public function isDockerUp($unix_socket = '', $api_version = '');

}
