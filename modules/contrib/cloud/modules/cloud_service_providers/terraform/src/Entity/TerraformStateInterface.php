<?php

namespace Drupal\terraform\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a State entity.
 *
 * @ingroup terraform
 */
interface TerraformStateInterface extends ContentEntityInterface, EntityOwnerInterface {

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
  public function getStateId();

  /**
   * {@inheritdoc}
   */
  public function setStateId($state_id);

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
  public function getSerialNo();

  /**
   * {@inheritdoc}
   */
  public function setSerialNo($serial_no);

  /**
   * {@inheritdoc}
   */
  public function getDetail();

  /**
   * {@inheritdoc}
   */
  public function setDetail($detail);

}
