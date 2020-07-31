<?php

namespace Drupal\Tests\openstack\Functional;

use Drupal\cloud\Entity\CloudConfig;
use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Tests\cloud\Functional\CloudTestBase;
use Drupal\Tests\openstack\Traits\OpenStackTestEntityTrait;
use Drupal\Tests\openstack\Traits\OpenStackTestFormDataTrait;
use Drupal\Tests\openstack\Traits\OpenStackTestMockTrait;

/**
 * Base Test Case class for OpenStack.
 */
abstract class OpenStackTestBase extends CloudTestBase {

  use OpenStackTestEntityTrait;
  use OpenStackTestFormDataTrait;
  use OpenStackTestMockTrait;

  /**
   * The region.
   *
   * @var string
   */
  protected $cloudRegion;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'cloud',
    'openstack',
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
   *   The cloud service provide bundle type ('openstack').
   *
   * @return \Drupal\cloud\Entity\CloudConfig
   *   The cloud service provider (CloudConfig) entity.
   */
  protected function createCloudContext($bundle = 'openstack'): CloudContentEntityBase {
    $random = $this->random;
    $cloud_context_name = strtolower($random->name(8));
    $this->cloudContext = "{$cloud_context_name}_regionone";

    return $this->createTestEntity(CloudConfig::class, [
      'type'               => $bundle,
      'cloud_context'      => $cloud_context_name,
      'name'               => $cloud_context_name,
      'label'              => "OpenStack RegionOne - {$random->name(8, TRUE)}",
      'field_account_id'   => '',
      'field_api_endpoint' => 'http://openstack.endpoint:8788',
      'field_os_region'    => 'RegionOne',
      'field_access_key'   => $random->name(20, TRUE),
      'field_secret_key'   => $random->name(40, TRUE),
    ]);
  }

  /**
   * Test bulk operation for entities.
   *
   * @param string $type
   *   The name of the entity type. For example, instance.
   * @param array $entities
   *   The entities.
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
    $path_prefix = '/clouds/openstack'): void {

    $this->runTestEntityBulkImpl(
      $type,
      $entities,
      $operation,
      $passive_operation,
      $path_prefix
    );
  }

}
