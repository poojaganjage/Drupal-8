<?php

namespace Drupal\Tests\terraform\Functional;

use Drupal\Tests\cloud\Traits\CloudConfigTestEntityTrait;

/**
 * Tests Terraform variable.
 *
 * @group Terraform
 */
class TerraformVariableTest extends TerraformTestBase {

  use CloudConfigTestEntityTrait;

  public const TERRAFORM_VARIABLE_REPEAT_COUNT = 1;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Add a workspace.
    $workspaces = $this->createWorkspacesRandomTestFormData();
    $this->createWorkspaceTestEntity($workspaces[0]);
  }

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list terraform variable',
      'add terraform variable',
      'view terraform variable',
      'edit terraform variable',
      'delete terraform variable',
    ];
  }

  /**
   * Tests CRUD for Variable.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testVariable(): void {

    $cloud_context = $this->cloudContext;

    // List Variable for Terraform.
    $this->drupalGet("/clouds/terraform/$cloud_context/workspace/1/variable");
    $this->assertNoErrorMessage();

    // Add a new Variable.
    $add = $this->createVariableTestFormData(self::TERRAFORM_VARIABLE_REPEAT_COUNT);
    for ($i = 0; $i < self::TERRAFORM_VARIABLE_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addVariableMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/terraform/$cloud_context/workspace/1/variable/add",
        $add[$i],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Variable', '%label' => $add[$i]['attribute_key']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/terraform/$cloud_context/workspace/1/variable");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['attribute_key']);
    }

    for ($i = 0, $num = 1; $i < self::TERRAFORM_VARIABLE_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all variables listing exists.
      $this->drupalGet("/clouds/terraform/$cloud_context/workspace/1/variable");
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['attribute_key']);
      }
    }

    // Delete Variable.
    for ($i = 0, $num = 1; $i < self::TERRAFORM_VARIABLE_REPEAT_COUNT; $i++, $num++) {

      $this->deleteVariableMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/terraform/$cloud_context/workspace/1/variable/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Variable', '@label' => $add[$i]['attribute_key']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/terraform/$cloud_context/workspace/1/variable");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['attribute_key']);
    }
  }

  /**
   * Tests deleting workspaces with bulk operation.
   *
   * @throws \Exception
   */
  public function testVariableBulk(): void {
    for ($i = 0; $i < self::TERRAFORM_VARIABLE_REPEAT_COUNT; $i++) {
      // Create Variables.
      $variables = $this->createVariablesRandomTestFormData();
      $entities = [];
      foreach ($variables ?: [] as $variable) {
        $entities[] = $this->createVariableTestEntity($variable);
      }
      $this->deleteVariableMockData($variables[0]);
      $this->runTestVariableyBulk('variable', $entities);
    }
  }

  /**
   * Test bulk operation for variables.
   *
   * @param string $type
   *   The name of the entity type. For example, instance.
   * @param array $entities
   *   The data of entities.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  private function runTestVariableyBulk(
    $type,
    array $entities): void {

    $operation = 'delete';
    $passive_operation = 'deleted';
    $path_prefix = '/clouds/terraform';

    $cloud_context = $this->cloudContext;
    $entity_count = count($entities);

    $entity_type_id = $entities[0]->getEntityTypeId();
    $data = [];
    $data['action'] = "${entity_type_id}_delete_action";

    $this->drupalGet("$path_prefix/$cloud_context/workspace/1/$type");

    $checkboxes = $this->cssSelect('input[type=checkbox]');
    foreach ($checkboxes ?: [] as $checkbox) {
      if ($checkbox->getAttribute('name') === NULL) {
        continue;
      }

      $data[$checkbox->getAttribute('name')] = $checkbox->getAttribute('value');
    }

    // Confirm.
    $this->drupalPostForm(
      "$path_prefix/$cloud_context/workspace/1/$type",
      $data,
      $this->t('Apply to selected items')
    );
    $this->assertNoErrorMessage();

    $message = \Drupal::translation()->formatPlural($entity_count,
      'Are you sure you want to @operation this @singular?',
      'Are you sure you want to @operation these @plural?', [
        '@operation' => $operation,
        '@singular' => $entities[0]->getEntityType()->getSingularLabel(),
        '@plural' => $entities[0]->getEntityType()->getPluralLabel(),
      ]
    );

    $this->assertSession()->pageTextContains($message);
    foreach ($entities ?: [] as $entity_data) {
      $entity_name = $entity_data->label();
      $this->assertSession()->pageTextContains($entity_name);
    }

    // Operation.
    $operation_upper = ucfirst($operation);
    $this->drupalPostForm(
      "$path_prefix/$cloud_context/workspace/1/$type/${operation}_multiple",
      [],
      $operation_upper
    );

    $this->assertNoErrorMessage();

    foreach ($entities ?: [] as $entity_data) {
      $this->assertSession()->pageTextContains(
        $this->t('The @type @label has been @passive_operation.', [
          '@type' => $entity_data->getEntityType()->getSingularLabel(),
          '@label' => $entity_data->label(),
          '@passive_operation' => $passive_operation,
        ])
      );
    }

    $passive_operation_upper = ucfirst($passive_operation);
    $message = \Drupal::translation()->formatPlural($entity_count,
      $this->t('@passive_operation_upper @entity_count item.', [
        '@passive_operation_upper' => $passive_operation_upper,
        '@entity_count' => $entity_count,
      ]),
      $this->t('@passive_operation_upper @entity_count items.', [
        '@passive_operation_upper' => $passive_operation_upper,
        '@entity_count' => $entity_count,
      ])
    );
    $this->assertSession()->pageTextContains($message);
  }

}
