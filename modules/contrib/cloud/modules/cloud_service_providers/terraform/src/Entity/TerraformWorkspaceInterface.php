<?php

namespace Drupal\terraform\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Workspace entity.
 *
 * @ingroup terraform
 */
interface TerraformWorkspaceInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getWorkspaceId();

  /**
   * {@inheritdoc}
   */
  public function setWorkspaceId($workspace_id);

  /**
   * {@inheritdoc}
   */
  public function getAutoApply();

  /**
   * {@inheritdoc}
   */
  public function setAutoApply($auto_apply);

  /**
   * {@inheritdoc}
   */
  public function getTerraformVersion();

  /**
   * {@inheritdoc}
   */
  public function setTerraformVersion($terraform_version);

  /**
   * {@inheritdoc}
   */
  public function getWorkingDirectory();

  /**
   * {@inheritdoc}
   */
  public function setWorkingDirectory($working_directory);

  /**
   * {@inheritdoc}
   */
  public function getLocked();

  /**
   * {@inheritdoc}
   */
  public function setLocked($locked);

  /**
   * {@inheritdoc}
   */
  public function getCurrentRunId();

  /**
   * {@inheritdoc}
   */
  public function setCurrentRunId($current_run_id);

  /**
   * {@inheritdoc}
   */
  public function getCurrentRunStatus();

  /**
   * {@inheritdoc}
   */
  public function setCurrentRunStatus($current_run_status);

  /**
   * {@inheritdoc}
   */
  public function getVcsRepoIdentifier();

  /**
   * {@inheritdoc}
   */
  public function setVcsRepoIdentifier($vcs_repo_identifier);

  /**
   * {@inheritdoc}
   */
  public function getOauthTokenId();

  /**
   * {@inheritdoc}
   */
  public function setOauthTokenId($oauth_token_id);

  /**
   * {@inheritdoc}
   */
  public function getVcsRepoBranch();

  /**
   * {@inheritdoc}
   */
  public function setVcsRepoBranch($vcs_repo_branch);

  /**
   * {@inheritdoc}
   */
  public function getAwsCloud();

  /**
   * {@inheritdoc}
   */
  public function setAwsCloud($aws_cloud);

}
