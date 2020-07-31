<?php

namespace Drupal\Tests\aws_cloud\Functional\cloud\config;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Tests\aws_cloud\Traits\AwsCloudTestFormDataTrait;
use Drupal\Tests\aws_cloud\Traits\AwsCloudTestMockTrait;
use Drupal\Tests\cloud\Functional\cloud\config\CloudConfigTestBase;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests cloud service provider (CloudConfig).
 *
 * @group Cloud
 */
class AwsCloudConfigTest extends CloudConfigTestBase {

  use AwsCloudTestFormDataTrait;
  use AwsCloudTestMockTrait;

  /**
   * AWS_CLOUD_CONFIG_REPEAT_COUNT.
   *
   * @var int
   */
  public const AWS_CLOUD_CONFIG_REPEAT_COUNT = 1;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'cloud',
    'aws_cloud',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'administer cloud service providers',
      'add cloud service providers',
      'edit cloud service providers',
      'edit own cloud service providers',
      'delete cloud service providers',
      'delete own cloud service providers',
      'view unpublished cloud service providers',
      'view own unpublished cloud service providers',
      'view published cloud service providers',
      'view own published cloud service providers',
      'access dashboard',
      'view cloud service provider admin list',
      'list cloud server template',
      'administer aws_cloud',
      'view aws cloud instance type prices',
    ];
  }

  /**
   * Create cloud context.
   *
   * @param string $bundle
   *   The CloudConfig Bundle Type ('aws_cloud').
   *
   * @return \Drupal\cloud\Entity\CloudConfig
   *   The cloud service provider (CloudConfig) entity.
   */
  protected function createCloudContext($bundle = __CLASS__): CloudContentEntityBase {
    return parent::createCloudContext($this->getModuleName($bundle));
  }

  /**
   * Set up test.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUp() {

    parent::setUp();

    // Giving the parameters init(NULL, NULL): Invalidate $this->initMockData.
    $this->init(NULL, NULL);
    $this->initMockInstanceTypes();

    // Delete the existing $this->cloudContext since we test a CloudConfig
    // entities multiple deletion for themselves.
    if (!empty($this->cloudConfig)) {
      $this->cloudConfig->delete();
    }
  }

  /**
   * Tests CRUD for cloud service provider (CloudConfig) information.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Exception
   */
  public function testCloudConfig(): void {

    // Get all regions.
    $regions = \Drupal::service('aws_cloud.ec2')->getRegions();
    $target_region_index = random_int(0, count($regions) - 1);
    $target_region_display_name = $regions[array_keys($regions)[$target_region_index]];

    // Get AWS Cloud Instance Type Prices.
    $instance_types = aws_cloud_get_instance_types_by_region($target_region_display_name);

    // Set cache ker for AWS Cloud Instance Type Prices.
    $cache_key = _aws_cloud_get_instance_type_cache_key_by_region($target_region_display_name);

    // List AWS Cloud service providers for Amazon EC2.
    $this->drupalGet('/admin/structure/cloud_config');
    $this->assertNoErrorMessage();

    // Add a new Config information.
    $add = $this->createCloudConfigTestFormData();

    // Add random VPC information (Server-side).
    $vpcs = $this->createVpcsRandomTestFormData();
    $subnets = $this->createSubnetsRandomTestFormData();
    $this->updateVpcsAndSubnetsMockData($vpcs, $subnets);

    // Add random Image information (Server-side).
    $images = $this->createImagesRandomTestFormData();
    $this->updateImagesMockData($images);

    for ($i = 0; $i < self::AWS_CLOUD_CONFIG_REPEAT_COUNT; $i++) {
      // Delete cache of AWS Cloud Instance Type Prices.
      \Drupal::cache()->delete($cache_key);

      // We need to specify the 'view <CLOUD_CONTEXT>' permission.
      $label = "{$add[$i]['name[0][value]']} {$target_region_display_name}";
      $region_index = 0;

      foreach ($regions ?: [] as $region_name => $region_display_name) {
        if ($region_index === $target_region_index) {
          $add[$i]["regions[]"][] = $region_name;
        }
        $this->grantPermissions(
          Role::load(
            RoleInterface::AUTHENTICATED_ID),
            [
              'view ' . aws_cloud_form_cloud_config_aws_cloud_add_form_create_cloud_context(
                $add[$i]['name[0][value]'],
                $region_name),
            ]
        );

        $region_index++;
      }

      // These variables that are sent from a browser are specified in the form
      // in the browser, therefore we don't use the auto-generated test values
      // by createCloudConfigTestFormData.
      unset(
        $add[$i]['field_region'],
        $add[$i]['field_api_endpoint_uri'],
        $add[$i]['field_image_upload_url']
      );

      // Set 'field_get_price_list' value.
      $add[$i]['field_get_price_list[value]'] = array_rand([0, 1]);

      $this->drupalGet('/admin/structure/cloud_config/add');
      $this->assertNoErrorMessage();

      $this->drupalPostForm('/admin/structure/cloud_config/add',
        $add[$i],
        $this->t('Save'));

      // @FIXME: Not refactored due to the error message,
      // The module Google Applications is invalid. Please enable the module.
      $this->assertSession()->statusCodeEquals(200);
      $this->assertSession()->pageTextContains($this->t('Creating cloud service provider was performed successfully.'));

      $this->assertSession()->pageTextContains($label);

      // Make sure listing for '/admin/structure/cloud_config'.
      $this->drupalGet('/admin/structure/cloud_config');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkExists($label);

      // Make sure listing for '/clouds'.
      $this->drupalGet('/clouds');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkExists($label);

      // Make sure listing for '/admin/config/services/cloud/aws_cloud'.
      $this->drupalGet('/admin/config/services/cloud/aws_cloud');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkExists($label);

      // Make sure listing of AWS Cloud Instance Type Prices.
      $cloud_context = aws_cloud_form_cloud_config_aws_cloud_add_form_create_cloud_context(
        $add[$i]['name[0][value]'],
        $add[$i]['regions[]'][0]);
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance_type_price");

      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($this->t('AWS Cloud Instance Type Prices'));
      foreach ($instance_types ?: [] as $instance_type_data) {
        $parts = explode(':', $instance_type_data);

        $add[$i]['field_get_price_list[value]'] === 1
          ? $this->assertSession()->pageTextContains($parts[0])
          : $this->assertSession()->pageTextNotContains($parts[0]);
      }

      // Try to add an AWS Cloud service provider w/ the same input data.
      $this->drupalGet('/admin/structure/cloud_config/add');
      $this->assertNoErrorMessage();

      $this->drupalPostForm('/admin/structure/cloud_config/add',
        $add[$i],
        $this->t('Save'));

      // Make sure if the error is reported or not.
      $this->assertErrorMessage();
      $this->assertSession()->pageTextContains($this->t('The cloud service provider(s) already exists as the same ID: @regions', [
        '@regions' => aws_cloud_form_cloud_config_aws_cloud_add_form_create_cloud_context(
          $add[$i]['name[0][value]'],
          array_keys($regions)[$target_region_index]
        ),
      ]));
    }

    // Edit Config case.
    $edit = $this->createCloudConfigTestFormData();
    // This is CloudConfig test case, so we don't require default
    // $this->cloudContext, which has been already deleted in this setUp().
    // The entity number of $this->cloudContext was '1'.  Therefore the entity
    // number starts from '2', not '1', here.
    $cloud_config_prefix = $edit[0]['name[0][value]'];
    for ($i = 0, $num = 2; $i < self::AWS_CLOUD_CONFIG_REPEAT_COUNT; $i++) {
      $label = "${cloud_config_prefix} ${target_region_display_name}";
      $edit[$i]['name[0][value]'] = $label;

      // Set 'field_get_price_list' value.
      $edit[$i]['field_get_price_list[value]'] = array_rand([0, 1]);

      // Get AWS Cloud Instance Type Prices.
      $instance_types = aws_cloud_get_instance_types_by_region($edit[$i]['field_get_price_list[value]']);
      // Set cache ker for AWS Cloud Instance Type Prices.
      $cache_key = _aws_cloud_get_instance_type_cache_key_by_region($edit[$i]['field_get_price_list[value]']);
      // Delete cache of AWS Cloud Instance Type Prices.
      \Drupal::cache()->delete($cache_key);

      $this->drupalPostForm("/admin/structure/cloud_config/${num}/edit",
        $edit[$i],
        $this->t('Save')
      );
      // @FIXME: Not refactored due to the error message,
      // An error occurred: Cannot read credentials from /var/www/html/web/
      // sites/simpletest/85990801/private/aws_cloud/.aws/cloud_config_xxx.ini.
      $this->assertSession()->statusCodeEquals(200);
      $this->assertSession()->pageTextNotContains($this->t('Warning message'));

      $this->assertSession()->pageTextContains($label);

      // Make sure listing for '/admin/structure/cloud_config'.
      $this->drupalGet('/admin/structure/cloud_config');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkExists($label);

      // Make sure listing for '/clouds'.
      $this->drupalGet('/clouds');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkExists($label);

      // Make sure listing for '/admin/config/services/cloud/aws_cloud'.
      $this->drupalGet('/admin/config/services/cloud/aws_cloud');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkExists($label);

      // Make sure listing of AWS Cloud Instance Type Prices.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance_type_price");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($this->t('AWS Cloud Instance Type Prices'));
      foreach ($instance_types ?: [] as $instance_type_data) {
        $parts = explode(':', $instance_type_data);

        $edit[$i]['field_get_price_list[value]'] === 1
          ? $this->assertSession()->pageTextContains($parts[0])
          : $this->assertSession()->pageTextNotContains($parts[0]);
      }
    }

    // Delete Config Items.
    // Ditto. This is CloudConfig test case, so we don't require default
    // $this->cloudContext, which has been already deleted in this setUp().
    // The entity number of $this->cloudContext was '1'.  Therefore the entity
    // number starts from '2', not '1', here.
    for ($i = 0, $num = 2; $i < self::AWS_CLOUD_CONFIG_REPEAT_COUNT; $i++, $num++) {
      $this->drupalGet("/admin/structure/cloud_config/${num}/delete");
      $this->drupalPostForm("/admin/structure/cloud_config/${num}/delete",
        [],
        $this->t('Delete'));

      $this->assertNoErrorMessage();
    }
  }

  /**
   * Tests deleting cloud service provider (CloudConfig) with bulk operation.
   *
   * @throws \Exception
   */
  public function testCloudConfigBulk(): void {
    $this->runTestEntityBulk('aws_cloud');
  }

  /**
   * Tests Redirect for cloud service provider (CloudConfig) information.
   */
  public function testCloudConfigRedirect(): void {
    try {
      $this->repeatTestCloudConfigRedirect(self::AWS_CLOUD_CONFIG_REPEAT_COUNT);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

}
