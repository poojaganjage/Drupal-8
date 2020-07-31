<?php

namespace Drupal\Tests\cloud_budget\Functional;

use Drupal\cloud\Entity\CloudConfig;
use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Tests\cloud\Functional\CloudTestBase;
use Drupal\Tests\cloud_budget\Traits\CloudBudgetTestEntityTrait;
use Drupal\Tests\cloud_budget\Traits\CloudBudgetTestFormDataTrait;
use Drupal\Tests\cloud_budget\Traits\CloudBudgetTestMockTrait;

/**
 * Base Test Case class for Cloud Budget.
 */
abstract class CloudBudgetTestBase extends CloudTestBase {

  use CloudBudgetTestFormDataTrait;
  use CloudBudgetTestMockTrait;
  use CloudBudgetTestEntityTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'cloud',
    'k8s',
    'cloud_budget',
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
   *   The CloudConfig Bundle Type ('k8s').  This is used for `cloud_budget'.
   *
   * @return \Drupal\cloud\Entity\CloudConfig
   *   The cloud service provider (CloudConfig) entity.
   */
  protected function createCloudContext($bundle = 'k8s'): CloudContentEntityBase {
    $random = $this->random;
    $this->cloudContext = $random->name(8);
    return $this->createTestEntity(CloudConfig::class, [
      'type'             => $bundle,
      'cloud_context'    => $this->cloudContext,
      'name'             => 'K8s - ' . $random->name(8, TRUE),
      'field_api_server' => 'https://www.test-k8s.com',
      'field_token'      => $random->name(128, TRUE),
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
    $path_prefix = '/clouds/budget'): void {

    $this->runTestEntityBulkImpl(
      $type,
      $entities,
      $operation,
      $passive_operation,
      $path_prefix
    );
  }

}
