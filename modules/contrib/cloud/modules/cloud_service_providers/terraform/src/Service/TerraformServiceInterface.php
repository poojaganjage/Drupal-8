<?php

namespace Drupal\terraform\Service;

/**
 * Terraform service interface.
 */
interface TerraformServiceInterface {

  /**
   * Set the cloud context.
   *
   * @param string $cloud_context
   *   Cloud context string.
   */
  public function setCloudContext($cloud_context);

  /**
   * Describe workspaces.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function describeWorkspaces(array $params = []);

  /**
   * Create workspace.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createWorkspace(array $params);

  /**
   * Delete workspace.
   *
   * @param string $name
   *   The mame of the workspace.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deleteWorkspace($name);

  /**
   * Describe workspace runs.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function describeRuns(array $params = []);

  /**
   * Apply workspace run.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function applyRun(array $params = []);

  /**
   * Update logs of workspace run.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updateRunLogs(array $params = []);

  /**
   * {@inheritdoc}
   */
  public function showPlan($plan_id);

  /**
   * {@inheritdoc}
   */
  public function showApply($apply_id);

  /**
   * Describe workspace states.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function describeStates(array $params = []);

  /**
   * Describe workspace variables.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function describeVariables(array $params = []);

  /**
   * Create workspace variables.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createVariable(array $params);

  /**
   * Update workspace variable.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function patchVariable(array $params);

  /**
   * Delete workspace variable.
   *
   * @param string $name
   *   Parameter name to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deleteVariable($name);

  /**
   * Method to clear all entities out of the system.
   */
  public function clearAllEntities();

  /**
   * Create queue items for update resources queue.
   */
  public function createResourceQueueItems();

  /**
   * Update the Workspaces.
   *
   * Delete old Workspace entities, query the api for updated entities and store
   * them as Workspace entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale entities.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateWorkspaces(array $params = [], $clear = TRUE);

  /**
   * Update the Runs.
   *
   * Delete old Run entities, query the api for updated entities and store
   * them as Run entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale entities.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateRuns(array $params = [], $clear = TRUE);

  /**
   * Update the States.
   *
   * Delete old State entities, query the api for updated entities and store
   * them as State entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale entities.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateStates(array $params = [], $clear = TRUE);

  /**
   * Update the Variables.
   *
   * Delete old Variable entities, query the api for updated entities and store
   * them as Variable entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale entities.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateVariables(array $params = [], $clear = TRUE);

  /**
   * Update all Runs.
   *
   * Delete old Run entities, query the api for updated entities and store
   * them as Run entities.
   */
  public function updateAllRuns();

  /**
   * Update all States.
   *
   * Delete old State entities, query the api for updated entities and store
   * them as State entities.
   */
  public function updateAllStates();

  /**
   * Update all Variables.
   *
   * Delete old Variable entities, query the api for updated entities and store
   * them as Variable entities.
   */
  public function updateAllVariables();

}
