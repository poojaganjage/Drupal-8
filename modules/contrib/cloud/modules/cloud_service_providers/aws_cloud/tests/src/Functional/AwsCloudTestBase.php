<?php

namespace Drupal\Tests\aws_cloud\Functional;

use Drupal\cloud\Entity\CloudConfig;
use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Tests\aws_cloud\Traits\AwsCloudTestEntityTrait;
use Drupal\Tests\aws_cloud\Traits\AwsCloudTestFormDataTrait;
use Drupal\Tests\aws_cloud\Traits\AwsCloudTestMockTrait;
use Drupal\Tests\cloud\Functional\CloudTestBase;

/**
 * Base Test Case class for AWS cloud.
 */
abstract class AwsCloudTestBase extends CloudTestBase {

  use AwsCloudTestEntityTrait;
  use AwsCloudTestFormDataTrait;
  use AwsCloudTestMockTrait;

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
    'aws_cloud',
    'gapps',
  ];

  /**
   * Set up test.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUp() {

    parent::setUp();

    $this->init(__CLASS__, $this);
    $this->initMockInstanceTypes();
    $this->initMockGoogleSpreadsheetService();
  }

  /**
   * Create cloud context.
   *
   * @param string $bundle
   *   The cloud service provide bundle type ('aws_cloud').
   *
   * @return \Drupal\cloud\Entity\CloudConfig
   *   The cloud service provider (CloudConfig) entity.
   *
   * @throws \Exception
   */
  protected function createCloudContext($bundle = 'aws_cloud'): CloudContentEntityBase {
    $random = $this->random;
    $this->cloudContext = $random->name(8);

    // Set the cloud region so it is available.
    $num = random_int(1, 2);
    $this->cloudRegion = "us-west-$num";

    return $this->createTestEntity(CloudConfig::class, [
      'type'                    => $bundle,
      'cloud_context'           => $this->cloudContext,
      'label'                   => "Amazon EC2 US West ($num) - {$random->name(8, TRUE)}",
      'field_description'       => "{$this->cloudRegion}: " . date('Y/m/d H:i:s - D M j G:i:s T Y') . $random->string(64, TRUE),
      'field_region'            => "us-west-$num",
      'field_account_id'        => $random->name(16, TRUE),
      'field_access_key'        => $random->name(20, TRUE),
      'field_secret_key'        => $random->name(40, TRUE),
      'field_api_endpoint_uri'  => "https://ec2.us-west-${num}.amazonaws.com",
      'field_api_version'       => 'latest',
      'field_image_upload_url'  => "https://ec2.us-west-${num}.amazonaws.com",
      'field_x_509_certificate' => $random->string(255, TRUE),
      'field_automatically_assign_vpc' => '0',
      'field_get_price_list' => '0',
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
    $path_prefix = '/clouds/aws_cloud'): void {

    $this->runTestEntityBulkImpl(
      $type,
      $entities,
      $operation,
      $passive_operation,
      $path_prefix
    );
  }

}
