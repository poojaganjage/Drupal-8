<?php

namespace Drupal\terraform\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Variable entity.
 *
 * @ingroup terraform
 */
interface TerraformVariableInterface extends ContentEntityInterface, EntityOwnerInterface {

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
  public function getVariableId();

  /**
   * {@inheritdoc}
   */
  public function setVariableId($variable_id);

  /**
   * {@inheritdoc}
   */
  public function getAttributeKey();

  /**
   * {@inheritdoc}
   */
  public function setAttributeKey($attribute_key);

  /**
   * {@inheritdoc}
   */
  public function getAttributeValue();

  /**
   * {@inheritdoc}
   */
  public function setAttributeValue($attribute_value);

  /**
   * {@inheritdoc}
   */
  public function getCategory();

  /**
   * {@inheritdoc}
   */
  public function setCategory($category);

  /**
   * {@inheritdoc}
   */
  public function getSensitive();

  /**
   * {@inheritdoc}
   */
  public function setSensitive($sensitive);

  /**
   * {@inheritdoc}
   */
  public function getHcl();

  /**
   * {@inheritdoc}
   */
  public function setHcl($hcl);

}
