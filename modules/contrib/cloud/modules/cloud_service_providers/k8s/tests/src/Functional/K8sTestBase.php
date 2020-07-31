<?php

namespace Drupal\Tests\k8s\Functional;

use Drupal\cloud\Entity\CloudConfig;
use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Tests\cloud\Functional\CloudTestBase;
use Drupal\Tests\k8s\Traits\K8sTestFormDataTrait;
use Drupal\Tests\k8s\Traits\K8sTestMockTrait;
use Drupal\Tests\k8s\Traits\K8sTestEntityTrait;

/**
 * Base Test Case class for K8s.
 */
abstract class K8sTestBase extends CloudTestBase {

  use K8sTestFormDataTrait;
  use K8sTestMockTrait;
  use K8sTestEntityTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'cloud',
    'k8s',
  ];

  /**
   * The namespace.
   *
   * @var string
   */
  protected $namespace;

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
   *   The CloudConfig Bundle Type ('k8s')
   *
   * @return \Drupal\cloud\Entity\CloudConfig
   *   The cloud service provider (CloudConfig) entity.
   */
  protected function createCloudContext($bundle = 'k8s'): CloudContentEntityBase {
    $random = $this->random;
    $this->cloudContext = $random->name(8);
    // This is correct.  Not $this->createTestEntity since we have the same
    // method ($this->createTestEntity) to create a K8s resource.
    // We will pass the parameter to the parent same method.
    return parent::createTestEntity(CloudConfig::class, [
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
    $path_prefix = '/clouds/k8s'): void {

    $this->runTestEntityBulkImpl(
      $type,
      $entities,
      $operation,
      $passive_operation,
      $path_prefix
    );
  }

}
