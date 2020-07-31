<?php

namespace Drupal\Tests\k8s\Functional\cloud\config;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Tests\cloud\Functional\cloud\config\CloudConfigTestBase;
use Drupal\Tests\k8s\Traits\K8sTestFormDataTrait;

/**
 * Tests cloud service provider (CloudConfig).
 *
 * @group Cloud
 */
class K8sCloudConfigTest extends CloudConfigTestBase {

  use K8sTestFormDataTrait;

  /**
   * K8S_CLOUD_CONFIG_REPEAT_COUNT.
   *
   * @var int
   */
  public const K8S_CLOUD_CONFIG_REPEAT_COUNT = 2;

  /**
   * K8S_CLOUD_CONFIG_MENU_REPEAT_COUNT.
   *
   * @var int
   */
  public const K8S_CLOUD_CONFIG_MENU_REPEAT_COUNT = 3;

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
      'administer k8s',
      'list k8s node',
    ];
  }

  /**
   * Create cloud context.
   *
   * @param string $bundle
   *   The CloudConfig Bundle Type ('k8s').
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

    // For testK8sMenu.
    $this->drupalPlaceBlock('system_menu_block:main', [
      'region' => 'header',
      'theme' => 'claro',
    ]);

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

    // List K8s cloud service providers.
    $this->drupalGet('/admin/structure/cloud_config');
    $this->assertNoErrorMessage();

    // Add a new Config information.
    $add = $this->createCloudConfigTestFormData(self::K8S_CLOUD_CONFIG_REPEAT_COUNT);
    for ($i = 0; $i < self::K8S_CLOUD_CONFIG_REPEAT_COUNT; $i++) {

      $label = $add[$i]['name[0][value]'];

      unset($add[$i]['cloud_context']);
      $this->drupalGet('/admin/structure/cloud_config/add');
      $this->assertNoErrorMessage();

      // Test if API server field is empty.
      $_add = $add;
      $_add[$i]['field_api_server[0][value]'] = '';
      $this->drupalPostForm('/admin/structure/cloud_config/add',
        $_add[$i],
        $this->t('Save')
      );

      $this->assertErrorMessage();
      $this->assertSession()->pageTextContains($this->t('The API server field cannot be empty.'));

      // Test if API server field is empty.
      $_add = $add;
      $_add[$i]['field_token[0][value]'] = '';
      $this->drupalPostForm('/admin/structure/cloud_config/add',
        $_add[$i],
        $this->t('Save')
      );

      $this->assertErrorMessage();
      $this->assertSession()->pageTextContains($this->t('The token field cannot be empty.'));

      // Test if both API server field and Token field are empty.
      $_add = $add;
      $_add[$i]['field_api_server[0][value]'] = '';
      $_add[$i]['field_token[0][value]'] = '';
      $this->drupalPostForm('/admin/structure/cloud_config/add',
        $_add[$i],
        $this->t('Save')
      );

      $this->assertErrorMessage();
      $this->assertSession()->pageTextContains($this->t('The API server field cannot be empty.'));
      $this->assertSession()->pageTextContains($this->t('The token field cannot be empty.'));

      // Test the normal "Save" case.
      $this->drupalPostForm('/admin/structure/cloud_config/add',
        $add[$i],
        $this->t('Save')
      );

      $this->assertErrorMessage();
      $this->assertSession()->pageTextContains(strip_tags($this->t('The endpoint is unreachable. Please check the API server and token:')));
      $t_args = ['@type' => 'Cloud Service Provider', '%label' => $label];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing for '/admin/structure/cloud_config'.
      $this->drupalGet('/admin/structure/cloud_config');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkExists($label);

      // Make sure listing for '/clouds'.
      $this->drupalGet('/clouds');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkExists($label);

      // Make sure listing for '/admin/config/services/cloud/k8s'.
      $this->drupalGet('/admin/config/services/cloud/k8s');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkExists($label);
    }

    // Edit Config case.
    $edit = $this->createCloudConfigTestFormData(self::K8S_CLOUD_CONFIG_REPEAT_COUNT);

    // This is CloudConfig test case, so we don't require default
    // $this->cloudContext, which has been already deleted in this setUp().
    // The entity number of $this->cloudContext was '1'.  Therefore the entity
    // number starts from '2', not '1', here.
    for ($i = 0, $num = 2; $i < self::K8S_CLOUD_CONFIG_REPEAT_COUNT; $i++, $num++) {

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

      // Make sure listing for '/admin/config/services/cloud/k8s'.
      $this->drupalGet('/admin/config/services/cloud/k8s');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkExists($label);
    }

    // Delete Config Items.
    // Ditto. This is CloudConfig test case, so we don't require default
    // $this->cloudContext, which has been already deleted in this setUp().
    // The entity number of $this->cloudContext was '1'.  Therefore the entity
    // number starts from '2', not '1', here.
    for ($i = 0, $num = 2; $i < self::K8S_CLOUD_CONFIG_REPEAT_COUNT; $i++, $num++) {

      $label = $edit[$i]['name[0][value]'];

      $this->drupalPostForm("/admin/structure/cloud_config/${num}/delete",
        [],
        $this->t('Delete')
      );

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cloud Service Provider', '@label' => $label];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing for '/admin/structure/cloud_config'.
      $this->drupalGet('/admin/structure/cloud_config');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkNotExists($label);

      // Make sure listing for '/clouds'.
      $this->drupalGet('/clouds');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkNotExists($label);

      // Make sure listing for '/admin/config/services/cloud/k8s'.
      $this->drupalGet('/admin/config/services/cloud/k8s');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkNotExists($label);
    }
  }

  /**
   * Tests deleting cloud service provider (CloudConfig) with bulk operation.
   *
   * @throws \Exception
   */
  public function testCloudConfigBulk(): void {
    $this->runTestEntityBulk('k8s');
  }

  /**
   * Tests Redirect for cloud service provider (CloudConfig) information.
   */
  public function testCloudConfigRedirect(): void {
    try {
      $this->repeatTestCloudConfigRedirect(self::K8S_CLOUD_CONFIG_REPEAT_COUNT);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

  /**
   * Tests OpenStack menu based on cloud service provider.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testK8sMenu(): void {

    // List K8s cloud service providers.
    $this->drupalGet('/admin/structure/cloud_config');
    $this->assertNoErrorMessage();

    // Add a new Config information.
    $add = $this->createCloudConfigTestFormData(self::K8S_CLOUD_CONFIG_MENU_REPEAT_COUNT);
    for ($i = 0; $i < self::K8S_CLOUD_CONFIG_MENU_REPEAT_COUNT; $i++) {
      // We need to specify the 'view <CLOUD_CONTEXT>' permission.
      $label = $add[$i]['name[0][value]'];

      unset($add[$i]['cloud_context']);
      $this->drupalGet('/admin/structure/cloud_config/add');
      $this->assertNoErrorMessage();

      // Test the normal "Save" case.
      $this->drupalPostForm('/admin/structure/cloud_config/add',
        $add[$i],
        $this->t('Save')
      );

      $this->assertErrorMessage();
      $this->assertSession()->pageTextContains(strip_tags($this->t('The endpoint is unreachable. Please check the API server and token:')));
      $t_args = ['@type' => 'Cloud Service Provider', '%label' => $label];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));
      $this->assertSession()->pageTextContains($label);

      // Make sure listing for '/admin/structure/cloud_config'.
      $this->drupalGet('/admin/structure/cloud_config');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkExists($label);

      // Make sure listing for '/clouds'.
      $this->drupalGet('/clouds');
      $this->assertNoErrorMessage();
      $this->assertSession()->linkExists($label);

      $cloud_context = \Drupal::service('cloud')->generateCloudContext($label);

      // Verify OpenStack parent menu exist or not.
      $this->assertSession()->linkExists($this->t('K8s'));

      // Check that menu is exist or not.
      $this->assertSession()->linkByHrefExists("/clouds/k8s/{$cloud_context}/node");

      // Check that menu is accessible or not.
      $this->clickLink($label);
      $this->assertNoErrorMessage();
    }

    // Delete Config Items.
    // Ditto. This is CloudConfig test case, so we don't require default
    // $this->cloudContext, which has been already deleted in this setUp().
    // The entity number of $this->cloudContext was '1'.  Therefore the entity
    // number starts from '2', not '1', here.
    for ($i = 0, $num = 2; $i < self::K8S_CLOUD_CONFIG_MENU_REPEAT_COUNT; $i++, $num++) {
      $label = $add[$i]['name[0][value]'];
      $this->drupalGet("/admin/structure/cloud_config/${num}/delete");
      $this->drupalPostForm("/admin/structure/cloud_config/${num}/delete",
        [],
        $this->t('Delete'));

      // Verify K8s parent menu.
      $i === self::K8S_CLOUD_CONFIG_MENU_REPEAT_COUNT - 1
        ? $this->assertSession()->linkNotExistsExact($this->t('K8s'))
        : $this->assertSession()->linkExistsExact($this->t('K8s'));

      // Verify OpenStack dropdown menu doesn't exist.
      $this->assertSession()->linkNotExists($label);
    }

    // Verify first level menu OpenStack is removed or not.
    $this->assertSession()->linkNotExistsExact($this->t('K8s'));
    $this->assertSession()->linkExistsExact($this->t('Cloud service providers'));
    $this->assertSession()->linkExistsExact($this->t('Add Cloud Service Provider'));
  }

}
