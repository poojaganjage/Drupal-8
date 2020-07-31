<?php

namespace Drupal\vmware\Service;

/**
 * Vmware service interface.
 */
interface VmwareServiceInterface {

  /**
   * Set the cloud context.
   *
   * @param string $cloud_context
   *   Cloud context string.
   */
  public function setCloudContext($cloud_context);

  /**
   * Set credentials.
   *
   * @param array $credentials
   *   Credentials.
   */
  public function setCredentials(array $credentials);

  /**
   * Login to an VMware server.
   */
  public function login();

  /**
   * Describe VMs.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function describeVms(array $params = []);

  /**
   * Start VM.
   *
   * @param array $params
   *   Parameters array to send to API.
   */
  public function startVm(array $params = []);

  /**
   * Stop VM.
   *
   * @param array $params
   *   Parameters array to send to API.
   */
  public function stopVm(array $params = []);

  /**
   * Update the VMs.
   *
   * Delete old VM entities, query the api for updated entities and store
   * them as VM entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale entities.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateVms(array $params = [], $clear = TRUE);

}
