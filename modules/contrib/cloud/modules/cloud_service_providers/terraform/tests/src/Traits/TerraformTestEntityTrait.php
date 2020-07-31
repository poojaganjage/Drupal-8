<?php

namespace Drupal\Tests\terraform\Traits;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\terraform\Entity\TerraformWorkspace;
use Drupal\terraform\Entity\TerraformVariable;
use Drupal\Tests\cloud\Traits\CloudTestEntityTrait;

/**
 * The trait creating test entity for terraform testing.
 */
trait TerraformTestEntityTrait {

  use CloudTestEntityTrait;

  /**
   * Create a Terraform Workspace test entity.
   *
   * @param array $workspace
   *   The workspace data.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The role binding entity.
   */
  protected function createWorkspaceTestEntity(array $workspace): CloudContentEntityBase {
    return $this->createTestEntity(TerraformWorkspace::class, $workspace);
  }

  /**
   * Create a Terraform Variable test entity.
   *
   * @param array $variable
   *   The variable data.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The role binding entity.
   */
  protected function createVariableTestEntity(array $variable): CloudContentEntityBase {
    return $this->createTestEntity(TerraformVariable::class, $variable);
  }

}
