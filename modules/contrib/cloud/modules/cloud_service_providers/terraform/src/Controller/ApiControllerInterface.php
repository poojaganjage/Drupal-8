<?php

namespace Drupal\terraform\Controller;

use Drupal\terraform\Entity\TerraformRunInterface;
use Drupal\terraform\Entity\TerraformWorkspaceInterface;

/**
 * {@inheritdoc}
 */
interface ApiControllerInterface {

  /**
   * Update all workspaces.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateWorkspaceList($cloud_context);

  /**
   * Update all run.
   *
   * @param string $cloud_context
   *   Cloud context string.
   * @param \Drupal\terraform\Entity\TerraformWorkspaceInterface $terraform_workspace
   *   The terraform workspace entity.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateRunList($cloud_context, TerraformWorkspaceInterface $terraform_workspace);

  /**
   * Update all state.
   *
   * @param string $cloud_context
   *   Cloud context string.
   * @param \Drupal\terraform\Entity\TerraformWorkspaceInterface $terraform_workspace
   *   The terraform workspace entity.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateStateList($cloud_context, TerraformWorkspaceInterface $terraform_workspace);

  /**
   * Update all variable.
   *
   * @param string $cloud_context
   *   Cloud context string.
   * @param \Drupal\terraform\Entity\TerraformWorkspaceInterface $terraform_workspace
   *   The terraform workspace entity.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateVariableList($cloud_context, TerraformWorkspaceInterface $terraform_workspace);

  /**
   * Update a run.
   *
   * @param string $cloud_context
   *   Cloud context string.
   * @param \Drupal\terraform\Entity\TerraformWorkspaceInterface $terraform_workspace
   *   The terraform workspace entity.
   * @param \Drupal\terraform\Entity\TerraformRunInterface $terraform_run
   *   The terraform run entity.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateRun($cloud_context, TerraformWorkspaceInterface $terraform_workspace, TerraformRunInterface $terraform_run);

  /**
   * Get logs of a run.
   *
   * @param string $cloud_context
   *   Cloud context string.
   * @param \Drupal\terraform\Entity\TerraformWorkspaceInterface $terraform_workspace
   *   The terraform workspace entity.
   * @param \Drupal\terraform\Entity\TerraformRunInterface $terraform_run
   *   The terraform run entity.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function getRunLogs($cloud_context, TerraformWorkspaceInterface $terraform_workspace, TerraformRunInterface $terraform_run);

}
