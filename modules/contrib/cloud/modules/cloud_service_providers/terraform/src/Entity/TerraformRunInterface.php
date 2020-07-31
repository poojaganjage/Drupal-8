<?php

namespace Drupal\terraform\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Run entity.
 *
 * @ingroup terraform
 */
interface TerraformRunInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getTerraformWorkspaceId();

  /**
   * {@inheritdoc}
   */
  public function setTerraformWorkspaceId($terraform_workspace_id);

  /**
   * {@inheritdoc}
   */
  public function getRunId();

  /**
   * {@inheritdoc}
   */
  public function setRunId($run_id);

  /**
   * {@inheritdoc}
   */
  public function getMessage();

  /**
   * {@inheritdoc}
   */
  public function setMessage($message);

  /**
   * {@inheritdoc}
   */
  public function getStatus();

  /**
   * {@inheritdoc}
   */
  public function setStatus($status);

  /**
   * {@inheritdoc}
   */
  public function getSource();

  /**
   * {@inheritdoc}
   */
  public function setSource($source);

  /**
   * {@inheritdoc}
   */
  public function getTriggerReason();

  /**
   * {@inheritdoc}
   */
  public function setTriggerReason($trigger_reason);

  /**
   * {@inheritdoc}
   */
  public function setPlanId($plan_id);

  /**
   * {@inheritdoc}
   */
  public function getPlanLog();

  /**
   * {@inheritdoc}
   */
  public function setPlanLog($plan_log);

  /**
   * {@inheritdoc}
   */
  public function getApplyId();

  /**
   * {@inheritdoc}
   */
  public function setApplyId($apply_id);

  /**
   * {@inheritdoc}
   */
  public function getApplyLog();

  /**
   * {@inheritdoc}
   */
  public function setApplyLog($apply_log);

}
