<?php

namespace Drupal\Tests\vmware\Functional\cloud\config;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Tests\cloud\Functional\cloud\config\CloudConfigTestBase;
use Drupal\Tests\vmware\Traits\VmwareTestFormDataTrait;

/**
 * Tests cloud service provider (CloudConfig).
 *
 * @group Cloud
 */
class VmwareCloudConfigTest extends CloudConfigTestBase {

  use VmwareTestFormDataTrait;

  /**
   * VMWARE_CLOUD_CONFIG_REPEAT_COUNT.
   *
   * @var int
   */
  public const VMWARE_CLOUD_CONFIG_REPEAT_COUNT = 2;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'cloud',
    'vmware',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'administer cloud service providers',
      'view all cloud service providers',
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
      'administer vmware',
    ];
  }

  /**
   * Create cloud context.
   *
   * @param string $bundle
   *   The CloudConfig Bundle Type ('vmware').
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

    $this->init(__CLASS__, $this);

    // Delete the existing $this->cloudContext since we test a CloudConfig
    // entities multiple deletion for themselves.
    if (!empty($this->cloudConfig)) {
      $this->cloudConfig->delete();
    }
  }

  /**
   * Tests CRUD for cloud service provider information.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testCloudConfig(): void {

    // List VMware cloud service providers.
    $this->drupalGet('/admin/structure/cloud_config');
    $this->assertNoErrorMessage();

    // Add a new Config information.
    $add = $this->createCloudConfigTestFormData(self::VMWARE_CLOUD_CONFIG_REPEAT_COUNT);
    for ($i = 0; $i < self::VMWARE_CLOUD_CONFIG_REPEAT_COUNT; $i++) {

      $label = $add[$i]['name[0][value]'];

      unset($add[$i]['cloud_context']);
      $this->drupalGet('/admin/structure/cloud_config/add');
      $this->assertNoErrorMessage();

      // Test the normal "Save" case.
      $this->drupalPostForm('/admin/structure/cloud_config/add',
        $add[$i],
        $this->t('Save')
      );

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'cloud service provider', '%label' => $label];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

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
    $edit = $this->createCloudConfigTestFormData(self::VMWARE_CLOUD_CONFIG_REPEAT_COUNT);

    // This is CloudConfig test case, so we don't require default
    // $this->cloudContext, which has been already deleted in this setUp().
    // The entity number of $this->cloudContext was '1'.  Therefore the entity
    // number starts from '2', not '1', here.
    for ($i = 0, $num = 2; $i < self::VMWARE_CLOUD_CONFIG_REPEAT_COUNT; $i++, $num++) {

      $label = $edit[$i]['name[0][value]'];

      unset($edit[$i]['cloud_context']);

      $this->drupalPostForm("/admin/structure/cloud_config/${num}/edit",
        $edit[$i],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();

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
    for ($i = 0, $num = 2; $i < self::VMWARE_CLOUD_CONFIG_REPEAT_COUNT; $i++, $num++) {

      $this->drupalPostForm("/admin/structure/cloud_config/${num}/delete",
        [],
        $this->t('Delete')
      );

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'cloud service provider', '@label' => $edit[$i]['name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing for '/admin/structure/cloud_config'.
      $this->drupalGet('/admin/structure/cloud_config');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkNotExists($edit[$i]['name[0][value]']);

      // Make sure listing for '/clouds'.
      $this->drupalGet('/clouds');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkNotExists($edit[$i]['name[0][value]']);
    }
  }

  /**
   * Tests deleting cloud service provider (CloudConfig) with bulk operation.
   *
   * @throws \Exception
   */
  public function testCloudConfigBulk(): void {
    $this->runTestEntityBulk('vmware');
  }

  /**
   * Tests Redirect for cloud service provider (CloudConfig) information.
   */
  public function testCloudConfigRedirect(): void {
    try {
      $this->repeatTestCloudConfigRedirect(self::VMWARE_CLOUD_CONFIG_REPEAT_COUNT);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

}
