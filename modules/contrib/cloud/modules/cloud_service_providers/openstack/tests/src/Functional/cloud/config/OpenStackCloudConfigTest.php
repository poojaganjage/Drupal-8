<?php

namespace Drupal\Tests\openstack\Functional\cloud\config;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Tests\cloud\Functional\cloud\config\CloudConfigTestBase;
use Drupal\Tests\openstack\Traits\OpenStackTestFormDataTrait;
use Drupal\Tests\openstack\Traits\OpenStackTestMockTrait;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests cloud service provider (CloudConfig).
 *
 * @group Cloud
 */
class OpenStackCloudConfigTest extends CloudConfigTestBase {

  use OpenStackTestFormDataTrait;
  use OpenStackTestMockTrait;

  /**
   * OPENSTACK_CLOUD_CONFIG_REPEAT_COUNT.
   *
   * @var int
   */
  public const OPENSTACK_CLOUD_CONFIG_REPEAT_COUNT = 1;

  /**
   * OPENSTACK_CLOUD_CONFIG_MENU_REPEAT_COUNT.
   *
   * @var int
   */
  public const OPENSTACK_CLOUD_CONFIG_MENU_REPEAT_COUNT = 3;

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
      'administer openstack',
      'list openstack instances',
    ];
  }

  /**
   * Create cloud context.
   *
   * @param string $bundle
   *   The CloudConfig Bundle Type ('openstack').
   *
   * @return \Drupal\cloud\Entity\CloudConfig
   *   The cloud service provider (CloudConfig) entity.
   */
  protected function createCloudContext($bundle = __CLASS__): CloudContentEntityBase {
    return parent::createCloudContext($this->getModuleName($bundle));
  }

  /**
   * Set up test.
   */
  protected function setUp(): void {

    parent::setUp();

    $this->drupalPlaceBlock('system_menu_block:main', [
      'region' => 'header',
      'theme' => 'claro',
    ]);

    // Giving the parameters init(NULL, NULL): Invalidate $this->initMockData.
    $this->init(__CLASS__, $this);

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
   */
  public function testCloudConfig(): void {
    $region_name = 'RegionOne';

    // List OpenStack cloud service providers.
    $this->drupalGet('/admin/structure/cloud_config');
    $this->assertNoErrorMessage();

    // Add a new Config information.
    $add = $this->createOpenStackCloudConfigTestFormData(self::OPENSTACK_CLOUD_CONFIG_REPEAT_COUNT);
    for ($i = 0; $i < self::OPENSTACK_CLOUD_CONFIG_REPEAT_COUNT; $i++) {
      // We need to specify the 'view <CLOUD_CONTEXT>' permission.
      $label = $add[$i]['name[0][value]'];
      $this->grantPermissions(
        Role::load(
          RoleInterface::AUTHENTICATED_ID),
          [
            'view ' . aws_cloud_form_cloud_config_aws_cloud_add_form_create_cloud_context(
              $label,
              $region_name),
          ]
      );

      // Add new CloudConfig.
      $this->drupalGet('/admin/structure/cloud_config/add');
      $this->assertNoErrorMessage();

      // Test w/ a wrong region.
      $add[$i]['field_os_region[0][value]'] = 'RegionTwo';
      $regions = ['RegionName' => $region_name];
      $this->updateDescribeRegionsMockData($regions);
      $this->drupalPostForm('/admin/structure/cloud_config/add/openstack',
        $add[$i],
        $this->t('Save'));

      // Validate if 'Region is invalid' or not (Expect an error message on
      // purpose).
      $this->assertErrorMessage();
      $this->assertSession()->pageTextNotContains($this->t('Creating cloud service provider was performed successfully.'));
      $this->assertSession()->pageTextContains($this->t('Region is invalid. Please enter valid region.'));

      // Test w/ a correct region.
      $add[$i]['field_os_region[0][value]'] = 'RegionOne';
      $this->drupalPostForm('/admin/structure/cloud_config/add/openstack',
        $add[$i],
        $this->t('Save'));

      // Validate if an OpenStack cloud service provider is created
      // successfully or not (Expect no error message).
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($this->t('Region is invalid. Please enter valid region.'));
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
    }

    // Edit Config case.
    $edit = $this->createOpenStackCloudConfigTestFormData(self::OPENSTACK_CLOUD_CONFIG_REPEAT_COUNT);
    // This is CloudConfig test case, so we don't require default
    // $this->cloudContext, which has been already deleted in this setUp().
    // The entity number of $this->cloudContext was '1'.  Therefore the entity
    // number starts from '2', not '1', here.
    for ($i = 0, $num = 2; $i < self::OPENSTACK_CLOUD_CONFIG_REPEAT_COUNT; $i++, $num++) {
      $label = $edit[$i]['name[0][value]'];

      $this->drupalPostForm("/admin/structure/cloud_config/${num}/edit",
        $edit[$i],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();

      $this->assertSession()->pageTextContains($label);

      // Make sure listing for '/admin/structure/cloud_config'.
      $this->drupalGet('/admin/structure/cloud_config');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkExists($label);

      // Make sure listing for '/clouds'.
      $this->drupalGet('/clouds');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkExists($label);
    }

    // Delete Config Items.
    // Ditto. This is CloudConfig test case, so we don't require default
    // $this->cloudContext, which has been already deleted in this setUp().
    // The entity number of $this->cloudContext was '1'.  Therefore the entity
    // number starts from '2', not '1', here.
    for ($i = 0, $num = 2; $i < self::OPENSTACK_CLOUD_CONFIG_REPEAT_COUNT; $i++, $num++) {
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
    $this->runTestEntityBulk('openstack');
  }

  /**
   * Tests Redirect for cloud service provider (CloudConfig) information.
   */
  public function testCloudConfigRedirect(): void {
    try {
      $this->repeatTestCloudConfigRedirect(self::OPENSTACK_CLOUD_CONFIG_REPEAT_COUNT);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

  /**
   * Tests OpenStack menu based on cloud service provider.
   */
  public function testOpenStackMenu(): void {
    $region_name = 'RegionOne';

    // List OpenStack cloud service providers.
    $this->drupalGet('/admin/structure/cloud_config');
    $this->assertNoErrorMessage();

    // Add a new Config information.
    $add = $this->createOpenStackCloudConfigTestFormData(self::OPENSTACK_CLOUD_CONFIG_MENU_REPEAT_COUNT);
    for ($i = 0; $i < self::OPENSTACK_CLOUD_CONFIG_MENU_REPEAT_COUNT; $i++) {
      // We need to specify the 'view <CLOUD_CONTEXT>' permission.
      $label = $add[$i]['name[0][value]'];
      $this->grantPermissions(
        Role::load(
          RoleInterface::AUTHENTICATED_ID),
          [
            'view ' . aws_cloud_form_cloud_config_aws_cloud_add_form_create_cloud_context(
              $label,
              $region_name),
          ]
      );

      // Add new CloudConfig.
      $this->drupalGet('/admin/structure/cloud_config/add');
      $this->assertNoErrorMessage();

      $this->drupalPostForm('/admin/structure/cloud_config/add/openstack',
        $add[$i],
        $this->t('Save'));

      $this->assertNoErrorMessage();
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

      $cloud_context = aws_cloud_form_cloud_config_aws_cloud_add_form_create_cloud_context(
              $label,
              $region_name);

      // Verify OpenStack parent menu exist or not.
      $this->assertSession()->linkExists($this->t('OpenStack'));

      // Check that menu is exist or not.
      $this->assertSession()->linkByHrefExists("/clouds/openstack/{$cloud_context}/instance");

      // Check that menu is accessible or not.
      $this->clickLink($label);
      $this->assertNoErrorMessage();
    }

    // Delete Config Items.
    // Ditto. This is CloudConfig test case, so we don't require default
    // $this->cloudContext, which has been already deleted in this setUp().
    // The entity number of $this->cloudContext was '1'.  Therefore the entity
    // number starts from '2', not '1', here.
    for ($i = 0, $num = 2; $i < self::OPENSTACK_CLOUD_CONFIG_MENU_REPEAT_COUNT; $i++, $num++) {
      $label = $add[$i]['name[0][value]'];
      $this->drupalGet("/admin/structure/cloud_config/${num}/delete");
      $this->drupalPostForm("/admin/structure/cloud_config/${num}/delete",
        [],
        $this->t('Delete'));

      // Verify OpenStack parent menu.
      $i === self::OPENSTACK_CLOUD_CONFIG_MENU_REPEAT_COUNT - 1
        ? $this->assertSession()->linkNotExistsExact($this->t('OpenStack'))
        : $this->assertSession()->linkExistsExact($this->t('OpenStack'));

      // Verify OpenStack dropdown menu doesn't exist.
      $this->assertSession()->linkNotExists($label);
    }

    // Verify first level menu OpenStack is removed or not.
    $this->assertSession()->linkNotExistsExact($this->t('OpenStack'));
    $this->assertSession()->linkExistsExact($this->t('Cloud service providers'));
    $this->assertSession()->linkExistsExact($this->t('Add Cloud Service Provider'));
  }

}
