<?php

namespace Drupal\Tests\terraform\Functional;

use Drupal\Tests\cloud\Traits\CloudConfigTestEntityTrait;

/**
 * Tests Terraform workspace.
 *
 * @group Terraform
 */
class TerraformWorkspaceTest extends TerraformTestBase {

  use CloudConfigTestEntityTrait;

  public const TERRAFORM_WORKSPACE_REPEAT_COUNT = 1;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list terraform workspace',
      'add terraform workspace',
      'view terraform workspace',
      'edit terraform workspace',
      'delete terraform workspace',
    ];
  }

  /**
   * Tests CRUD for Workspace.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testWorkspace(): void {

    $cloud_context = $this->cloudContext;
    // List Workspace for Terraform.
    $this->drupalGet("/clouds/terraform/$cloud_context/workspace");
    $this->assertNoErrorMessage();

    // Add a new Workspace.
    $add = $this->createWorkspaceTestFormData(self::TERRAFORM_WORKSPACE_REPEAT_COUNT);
    for ($i = 0; $i < self::TERRAFORM_WORKSPACE_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addWorkspaceMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/terraform/$cloud_context/workspace/add",
        $add[$i],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Workspace', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/terraform/$cloud_context/workspace");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::TERRAFORM_WORKSPACE_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all workspace listing exists.
      $this->drupalGet('/clouds/terraform/workspace');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Delete Workspace.
    for ($i = 0, $num = 1; $i < self::TERRAFORM_WORKSPACE_REPEAT_COUNT; $i++, $num++) {

      $this->deleteWorkspaceMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/terraform/$cloud_context/workspace/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Workspace', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/terraform/$cloud_context/workspace");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting workspaces with bulk operation.
   *
   * @throws \Exception
   */
  public function testWorkspaceBulk(): void {

    for ($i = 0; $i < self::TERRAFORM_WORKSPACE_REPEAT_COUNT; $i++) {
      // Create Workspaces.
      $workspaces = $this->createWorkspacesRandomTestFormData();
      $entities = [];
      foreach ($workspaces ?: [] as $workspace) {
        $entities[] = $this->createWorkspaceTestEntity($workspace);
      }
      $this->deleteWorkspaceMockData($workspaces[0]);
      $this->runTestEntityBulk('workspace', $entities);
    }
  }

}
