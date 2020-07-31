<?php

namespace Drupal\Tests\cloud\Functional;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Random;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\cloud\Traits\CloudAssertionTrait;
use Drupal\Tests\cloud\Traits\CloudTestEntityTrait;

/**
 * Test case base class for a Cloud family module.
 *
 * When you want to create a new module, please take the following the steps:
 *
 * 1. Add two sub test base classes for your module based on:
 *
 * - Drupal\Tests\cloud\Functional\CloudTestBase and
 * - Drupal\Tests\cloud\Functional\cloud\config\CloudConfigTestBase
 *
 * That is,
 *
 * - Drupal\Tests\<module_name>\Functional\<<ModuleName>TestBase and
 * - Drupal\Tests\<<module_name>\Functional\cloud\config\<<ModuleName>ConfigTestBase
 *
 *   (e.g. For AwsCloud module, AwsCloudTestBase and AwsCloudConfigTestBase).
 *
 * NOTE:
 * - TestBase needs to have setUp which includes $this->init(__CLASS__, $this).
 * - ConfigTestBase needs to have setUp which includes $this->init(NULL, NULL).
 *
 * 2. Add three traits such as:
 *
 * - Drupal\Tests\<module_name>\Traits\<ModuleName>TestFormDataTrait
 * - Drupal\Tests\<module_name>\Traits\<ModuleName>TestEntityTrait
 * - Drupal\Tests\<module_name>\Traits\<<ModuleName>TestMockTrait
 *
 *   (e.g. AwsCloudTestTestEntityTrait, AwsCloudTestFormDataTrait,
 *    AwsCloudTestMockTrait).
 *
 * NOTE:
 * - TestFormDataTrait returns input form data for testing.
 * - TestEntityTrait returns dummy entities into Drupal's database.
 * - TestMockTrait returns objects from pretending cloud service provider API.
 *
 * 3. Add test case classes such as:
 *
 * - Drupal\Tests\<ModuleName>\<EntityClassName>Test
 *
 * NOTE:
 * - <EntityClassName>Test needs to implement three methods such as
 * createCloudContext(), getPermissions() and runTestEntityBulk().
 *
 * 4. Add Drupal\Tests\<ModuleName>\Functional\Module\InstallUninstallTest in
 * order to test install/uninstall your module.
 *
 * NOTE:
 * - You may want to copy the class:
 *
 * Drupal\Tests\cloud\Functional\Module\InstallUninstallTest.
 */
abstract class CloudTestBase extends BrowserTestBase {

  use CloudAssertionTrait;
  use CloudTestEntityTrait;
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'claro';

  /**
   * The profile to install as a basis for testing.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * The random object.
   *
   * @var \Drupal\Component\Utility\Random
   */
  protected $random;

  /**
   * The cloud context.
   *
   * @var string
   */
  protected $cloudContext;

  /**
   * The cloud context.
   *
   * @var \Drupal\cloud\Entity\CloudConfig
   */
  protected $cloudConfig;

  /**
   * The latest template variables.
   *
   * @var array
   */
  protected $latestTemplateVars;

  /**
   * Init setup.
   *
   * @param string $__class__
   *   The class name __CLASS__.
   * @param object $class
   *   The class object $this (::class).
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function init($__class__, $class): void {

    if (!$this->random) {
      $this->random = new Random();
    }

    // DO NOT modify the following setup procedure sequence.
    // 1. Set TRUE in "{module_name}_test_mode".
    $module_name = $this->getModuleName();
    $config = \Drupal::configFactory()->getEditable("${module_name}.settings");
    $config->set("${module_name}_test_mode", TRUE)
      ->save();

    // 2. Create $cloud_context.
    // @FIXME: createCloudContext returns CloudConfig entity, not $cloud_context.
    $this->cloudConfig = $this->createCloudContext();

    // 3. Initialize mock data.  When $this->initMockData(NULL, NULL) is
    // specified, skip this method call (do nothing).
    $this->initMockData($__class__, $class);

    // 4. Setup all permissions required by the test case.
    $perms = $this->getPermissions();

    // 5. Set a permission for view $cloud_context.
    $view_perms = ['view all cloud service providers', "view {$this->cloudConfig->getCloudContext()}"];
    $perms[] = $view_perms[array_rand($view_perms)];

    // 6. Login to the site.
    $web_user = $this->drupalCreateUser($perms);
    $this->drupalLogin($web_user);
  }

  /**
   * Get permissions of login user.
   *
   * @return array
   *   permissions of login user.
   */
  abstract protected function getPermissions(): array;

  /**
   * Create cloud context.
   *
   * @param string $bundle
   *   The cloud service provide bundle type.
   *
   * @return \Drupal\cloud\Entity\CloudConfig|null
   *   The cloud service provider (CloudConfig) entity.
   */
  abstract protected function createCloudContext($bundle): CloudContentEntityBase;

  /**
   * Init mock data.
   *
   * @param string $__class__
   *   The class name __CLASS__.
   * @param object $class
   *   The class object $this (::class).
   */
  private function initMockData($__class__, $class): void {

    // The parameters must be filled.  Otherwise, do nothing.
    if (empty($__class__) && empty($class)) {
      return;
    }

    $mock_data = [];
    $module_name = '';
    $this->latestTemplateVars = $this->getMockDataTemplateVars();

    foreach ([$__class__, get_class($class)] as $class_name) {
      $module_name = $this->getModuleName($class_name);
      $content = $this->getMockDataFileContent($class_name, $this->latestTemplateVars);
      if (!empty($content)) {
        $mock_data = array_merge($mock_data, Yaml::decode($content));
      }
    }

    $config = \Drupal::configFactory()->getEditable("${module_name}.settings");
    $config->set("${module_name}_mock_data", json_encode($mock_data))
      ->save();
  }

  /**
   * Get mock data.
   *
   * @return array
   *   mock data.
   */
  protected function getMockData(): array {
    return [];
  }

  /**
   * Get the content of mock data file.
   *
   * @param string $class_name
   *   The class name.
   * @param array $vars
   *   For returning variables.
   * @param string $suffix
   *   The suffix.
   *
   * @return \Drupal\Component\Render\MarkupInterface|string
   *   The content of mock data file content object.
   */
  protected function getMockDataFileContent($class_name, array $vars, $suffix = '') {

    $module_name = $this->getModuleName($class_name);
    $path = \Drupal::service('file_system')->realpath(drupal_get_path('module', $module_name)) . '/tests/mock_data';
    $pos = strpos($class_name, $module_name) + strlen($module_name);
    $path .= str_replace('\\', '/', substr($class_name, $pos)) . $suffix . '.yml';

    // If $path is not found, return an empty string ('').
    if (!file_exists($path)) {
      return '';
    }

    $twig = \Drupal::service('twig');
    return $twig->renderInline(file_get_contents($path), $vars);
  }

  /**
   * Get mock data from configuration.
   *
   * @return array
   *   mock data.
   */
  protected function getMockDataFromConfig(): array {

    $module_name = $this->getModuleName();
    $config = \Drupal::configFactory()->getEditable("${module_name}.settings");
    return json_decode($config->get("${module_name}_mock_data"), TRUE) ?? [];
  }

  /**
   * Update mock data in configuration.
   *
   * @param array $mock_data
   *   The mock data array.
   */
  protected function updateMockDataToConfig(array $mock_data): void {

    $module_name = $this->getModuleName();
    $config = \Drupal::configFactory()->getEditable("${module_name}.settings");
    $config->set("${module_name}_mock_data", json_encode($mock_data))
      ->save();
  }

  /**
   * Get variables in mock data template file.
   *
   * @return array
   *   variables in mock data template file.
   */
  protected function getMockDataTemplateVars(): array {
    return [];
  }

  /**
   * Reload mock data in configuration.
   */
  protected function reloadMockData(): void {

    $mock_data = $this->getMockDataFromConfig();
    $this->latestTemplateVars = $this->getMockDataTemplateVars();
    $file_content = $this->getMockDataFileContent(get_class($this), $this->latestTemplateVars);
    if (!empty($file_content)) {
      $mock_data = array_merge($mock_data, Yaml::decode($file_content));
    }
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Get a module name from a class name.
   *
   * @param string $class_name
   *   The class name.
   *
   * @return string
   *   The module name.
   */
  protected function getModuleName($class_name = ''): string {

    $class_name = $class_name ?: get_class($this);
    $prefix = 'Drupal\\Tests\\';
    $module_name_start_pos = strpos($class_name, $prefix) + strlen($prefix);
    $module_name_end_pos = strpos($class_name, '\\', $module_name_start_pos);
    return substr($class_name, $module_name_start_pos,
      $module_name_end_pos - $module_name_start_pos);
  }

  /**
   * A stub for bulk operation for entities. Should call runTestEntityBulkImpl.
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
   */
  abstract protected function runTestEntityBulk(
    $type,
    array $entities,
    $operation = 'delete',
    $passive_operation = 'deleted',
    $path_prefix = '/admin/structure'): void;

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
  protected function runTestEntityBulkImpl(
    $type,
    array $entities,
    $operation = 'delete',
    $passive_operation = 'deleted',
    $path_prefix = '/admin/structure'): void {

    // When '/admin/...' is specified in $path_prefix,
    // we skip to add $cloud_context since e.g. '/admin/structure/' is the
    // cloud service provider listing page.
    // 0 !== strpos(...) equals to !preg_match('/^admin\//, $path_prefix).
    $cloud_context = !empty($this->cloudContext)
    && 0 !== strpos($path_prefix, '/admin/')
      ? "/{$this->cloudContext}"
      : '';

    $entity_count = count($entities);

    $entity_type_id = $entities[0]->getEntityTypeId();
    $data = [];
    $data['action'] = "${entity_type_id}_${operation}_action";

    $this->drupalGet("$path_prefix$cloud_context/$type");

    $checkboxes = $this->cssSelect('input[type=checkbox]');
    foreach ($checkboxes ?: [] as $checkbox) {
      if ($checkbox->getAttribute('name') === NULL) {
        continue;
      }

      $data[$checkbox->getAttribute('name')] = $checkbox->getAttribute('value');
    }

    // Confirm.
    $this->drupalPostForm(
      "$path_prefix$cloud_context/$type",
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
      "$path_prefix$cloud_context/$type/${operation}_multiple",
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
