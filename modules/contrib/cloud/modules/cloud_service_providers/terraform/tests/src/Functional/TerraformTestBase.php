<?php

namespace Drupal\Tests\terraform\Functional;

use Drupal\cloud\Entity\CloudConfig;
use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Tests\cloud\Functional\CloudTestBase;
use Drupal\Tests\terraform\Traits\TerraformTestEntityTrait;
use Drupal\Tests\terraform\Traits\TerraformTestFormDataTrait;
use Drupal\Tests\terraform\Traits\TerraformTestMockTrait;

/**
 * Base Test Case class for Terraform.
 */
abstract class TerraformTestBase extends CloudTestBase {

  use TerraformTestEntityTrait;
  use TerraformTestFormDataTrait;
  use TerraformTestMockTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'cloud',
    'terraform',
  ];

  /**
   * Set up test.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUp() {

    parent::setUp();

    $this->init(__CLASS__, $this);
  }

  /**
   * Create cloud context.
   *
   * @param string $bundle
   *   The CloudConfig Bundle Type ('terraform').
   *
   * @return \Drupal\cloud\Entity\CloudConfig
   *   The cloud service provider (CloudConfig) entity.
   */
  protected function createCloudContext($bundle = 'terraform'): CloudContentEntityBase {
    $random = $this->random;
    $this->cloudContext = $random->name(8);
    return $this->createTestEntity(CloudConfig::class, [
      'type'               => $bundle,
      'cloud_context'      => $this->cloudContext,
      'name'               => sprintf('Terraform - %s - %s', date('Y/m/d H:i:s'), $random->name(8, TRUE)),
      'field_organization' => $random->name(32, TRUE),
      'field_api_token'    => $random->name(128, TRUE),
    ]);
  }

  /**
   * Test bulk operation for entities.
   *
   * @param string $type
   *   The name of the entity type. For example, instance.
   * @param array $entities
   *   The data of entities.
   * @param string $operation
   *   The operation.
   * @param string $passive_operation
   *   The passive voice of operation.
   * @param string $path_prefix
   *   The URL path of prefix.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  protected function runTestEntityBulk(
    $type,
    array $entities,
    $operation = 'delete',
    $passive_operation = 'deleted',
    $path_prefix = '/clouds/terraform'): void {

    $this->runTestEntityBulkImpl(
      $type,
      $entities,
      $operation,
      $passive_operation,
      $path_prefix
    );
  }

}
