<?php

namespace Drupal\Tests\cloud\Functional\Module;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\cloud\Traits\CloudAssertionTrait;
use Drupal\Tests\system\Functional\Module\ModuleTestBase;

/**
 * Install/uninstall module(s) and confirm table creation/deletion.
 */
abstract class CloudModuleTestBase extends ModuleTestBase {

  use CloudAssertionTrait;
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'claro';

  /**
   * Repeat install / uninstall for a test case.
   */
  public const CLOUD_MODULE_INSTALL_UNINSTALL_REPEAT_COUNT = 1;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['dblog'];

  /**
   * {@inheritdoc}
   */
  public static $excludedModules = ['filter'];

  /**
   * Tests that a fixed set of modules can be installed and uninstalled.
   */
  abstract public function testInstallUninstall(): void;

  /**
   * Tests that a fixed set of modules can be installed and uninstalled.
   *
   * @param string $module_name
   *   The module names to test install and uninstall.
   */
  protected function runInstallUninstall($module_name): void {
    try {
      $this->repeatAssertInstallUninstallModules([$module_name]);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

  /**
   * Tests that a fixed set of modules can be installed and uninstalled.
   *
   * @param array $modules
   *   The module names to test install and uninstall.
   * @param int $max_count
   *   Max test repeating count.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  protected function repeatAssertInstallUninstallModules(array $modules = ['cloud'], $max_count = self::CLOUD_MODULE_INSTALL_UNINSTALL_REPEAT_COUNT): void {
    for ($i = 0; $i < $max_count; $i++) {
      // Install and uninstall $module.
      $this->assertTestInstallUninstallModules($modules);
    }
  }

  /**
   * Repeating test cloud service provider redirect.
   *
   * @param array $modules
   *   The module names to test install and uninstall.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  protected function assertTestInstallUninstallModules(array $modules): void {

    $all_modules = $this->container->get('extension.list.module')->getList();

    // Test help on required modules, but do not test uninstalling.
    $required_modules = array_filter($all_modules, static function ($module) {
      if (!empty($module->info['required']) || $module->status === TRUE) {
        if ($module->info['package'] !== 'Testing' && empty($module->info['hidden'])) {
          return TRUE;
        }
      }
      return FALSE;
    });

    $required_modules['help'] = $all_modules['help'];

    // Test uninstalling without hidden, required, and already enabled modules.
    $all_modules = array_filter($all_modules, static function ($module) {
      return !(!empty($module->info['hidden'])
        || !empty($module->info['required'])
        || $module->status === TRUE
        || $module->info['package'] === 'Testing');
    });

    // Install the Help module, and verify it installed successfully.
    unset($all_modules['help']);
    $this->assertModuleNotInstalled('help');
    $edit = [];
    $edit['modules[help][enable]'] = TRUE;
    $this->drupalPostForm('admin/modules', $edit, $this->t('Install'));
    $this->assertSession()->pageTextContains('has been enabled');
    $this->assertModuleSuccessfullyInstalled('help');

    // Test help for the required modules.
    foreach ($required_modules ?: [] as $name => $module) {
      $this->assertHelp($name, $module->info['name']);
    }

    // Go through each module in the list and try to install and uninstall
    // it with its dependencies.
    foreach ($all_modules ?: [] as $name => $module) {

      $was_installed_list = \Drupal::moduleHandler()->getModuleList();

      // Skip if $name is not a target module to validate.
      foreach ($modules ?: [] as $module_name) {
        if ($name !== $module_name) {
          continue 2;
        }
      }

      // Start a list of modules that we expect to be installed this time.
      $modules_to_install = [$name];
      foreach (array_keys($module->requires) ?: [] as $dependency) {
        if (!empty($all_modules[$dependency])) {
          $modules_to_install[] = $dependency;
        }
      }

      // Check that each module is not yet enabled and does not have any
      // database tables yet.
      foreach ($modules_to_install ?: [] as $module_to_install) {
        $this->assertModuleNotInstalled($module_to_install);
      }

      // Install the module.
      $edit = [];
      $package = $module->info['package'];
      $edit["modules[${name}][enable]"] = TRUE;
      $this->drupalPostForm('admin/modules', $edit, $this->t('Install'));

      // Handle experimental modules, which require a confirmation screen.
      if ($package === 'Core (Experimental)') {
        $this->assertSession()->pageTextContains('Are you sure you wish to enable experimental modules?');
        if (count($modules_to_install) > 1) {
          // When there are experimental modules, needed dependencies do not
          // result in the same page title, but there will be expected text
          // indicating they need to be enabled.
          $this->assertSession()->pageTextContains('You must enable');
        }
        $this->drupalPostForm(NULL, [], $this->t('Continue'));
      }
      // Handle the case where modules were installed along with this one and
      // where we therefore hit a confirmation screen.
      elseif (count($modules_to_install) > 1) {
        // Verify that we are on the correct form and that the expected text
        // about enabling dependencies appears.
        $this->assertSession()->pageTextContains('Some required modules must be enabled');
        $this->assertSession()->pageTextContains('You must enable');
        $this->drupalPostForm(NULL, [], $this->t('Continue'));
      }

      // List the module display names to check the confirmation message.
      $module_names = [];
      foreach ($modules_to_install ?: [] as $module_to_install) {
        $module_names[] = $all_modules[$module_to_install]->info['name'];
      }
      $expected_text = \Drupal::translation()->formatPlural(count($module_names), 'Module @name has been enabled.', '@count modules have been enabled: @names.', [
        '@name' => $module_names[0],
        '@names' => implode(', ', $module_names),
      ]);
      $this->assertSession()->pageTextContains($expected_text);

      // Check that hook_modules_installed() was invoked with the expected list
      // of modules, that each module's database tables now exist, and that
      // appropriate messages appear in the logs.
      foreach ($modules_to_install ?: [] as $module_to_install) {
        $this->assertLogMessage('system', '%module module installed.', ['%module' => $module_to_install], RfcLogLevel::INFO);
        $this->assertModuleSuccessfullyInstalled($module_to_install);
      }

      // Verify the help page.
      $this->assertHelp($name, $module->info['name']);
      $now_installed_list = \Drupal::moduleHandler()->getModuleList();
      $added_modules = array_diff(array_keys($now_installed_list), array_keys($was_installed_list));
      // Remove filter module since it is disabled and cannot be uninstalled.
      $added_modules = array_diff($added_modules, self::$excludedModules);
      $this->drupalGet('admin/modules/uninstall');
      while ($added_modules) {
        $initial_count = count($added_modules);
        foreach ($added_modules ?: [] as $to_uninstall) {
          // See if we can currently uninstall this module (if its dependencies
          // have been uninstalled), and do so if we can.
          $field_name = "uninstall[$to_uninstall]";
          $has_checkbox = $this->xpath('//input[@type="checkbox" and @name="' . $field_name . '"]');
          $disabled = $this->xpath('//input[@type="checkbox" and @name="' . $field_name . '" and @disabled="disabled"]');

          if (!empty($has_checkbox) && empty($disabled)) {
            // This one is eligible for being uninstalled.
            $package = $all_modules[$to_uninstall]->info['package'];
            $this->assertSuccessfulUninstall($to_uninstall);
            $added_modules = array_diff($added_modules, [$to_uninstall]);
          }
        }

        // If we were not able to find a module to uninstall, fail and exit the
        // loop.
        $final_count = count($added_modules);
        if ($initial_count === $final_count) {
          $this->fail('Remaining modules could not be uninstalled for ' . $name);
          break;
        }
      }
    }

    // Uninstall the help module and put it back into the list of modules.
    $all_modules['help'] = $required_modules['help'];
    $this->assertSuccessfulUninstall('help');

    // Now that all modules have been tested, go back and try to enable them
    // all again at once. This tests two things:
    // - That each module can be successfully enabled again after being
    //   uninstalled.
    // - That enabling more than one module at the same time does not lead to
    //   any errors.
    // Currently some module cuases the error, so skip this assertion.
    // See also:
    // https://www.drupal.org/project/drupal/issues/3016131
    if (FALSE) {
      $this->assertEnableAllModules($all_modules);
    }
  }

  /**
   * Asserts that all module can be enabled.
   *
   * @param array $all_modules
   *   Name of the module to check.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  protected function assertEnableAllModules(array $all_modules): void {

    $edit = [];
    $experimental = FALSE;
    // Remove filter module since it is disabled and cannot be uninstalled.
    foreach ($all_modules ?: [] as $name => $module) {
      // Skip if $name is an excluded module name.
      foreach (self::$excludedModules ?: [] as $module_name) {
        if ($name === $module_name) {
          continue 2;
        }
      }

      $edit["modules[${name}][enable]"] = TRUE;
      // Track whether there is at least one experimental module.
      if ($module->info['package'] === 'Core (Experimental)') {
        $experimental = TRUE;
      }
    }

    $this->drupalPostForm('admin/modules', $edit, $this->t('Install'));

    // If there are experimental modules, click the confirm form.
    if ($experimental) {
      $this->assertSession()->pageTextContains('Are you sure you wish to enable experimental modules?');
      $this->drupalPostForm(NULL, [], $this->t('Continue'));
    }
    // The string tested here is translatable but we are only using a part of it
    // so using a translated string is wrong. Doing so would create a new string
    // to translate.
    $this->assertSession()->pageTextContains(new FormattableMarkup('@count modules have been enabled: ', ['@count' => count($all_modules)]));
  }

  /**
   * Asserts that a module is not yet installed.
   *
   * @param string $name
   *   Name of the module to check.
   */
  protected function assertModuleNotInstalled($name): void {
    $this->assertModules([$name], FALSE);
    // $this->assertModuleTablesDoNotExist($name);
  }

  /**
   * Asserts that a module was successfully installed.
   *
   * @param string $name
   *   Name of the module to check.
   */
  protected function assertModuleSuccessfullyInstalled($name): void {
    $this->assertModules([$name], TRUE);
    $this->assertModuleTablesExist($name);
    $this->assertModuleConfig($name);
  }

  /**
   * Uninstalls a module and asserts that it was done correctly.
   *
   * @param string $module
   *   The name of the module to uninstall.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  protected function assertSuccessfulUninstall($module): void {
    $edit = [];
    $edit["uninstall[${module}]"] = TRUE;
    $this->drupalPostForm('admin/modules/uninstall', $edit, $this->t('Uninstall'));
    $this->drupalPostForm(NULL, NULL, $this->t('Uninstall'));
    $this->assertSession()->pageTextContains(t('The selected modules have been uninstalled.'));
    $this->assertModules([$module], FALSE);

    // Check that the appropriate hook was fired and the appropriate log
    // message appears. (But don't check for the log message if the dblog
    // module was just uninstalled, since the {watchdog} table won't be there
    // anymore.)
    $this->assertLogMessage('system', '%module module uninstalled.', ['%module' => $module], RfcLogLevel::INFO);

    // Check that the module's database tabless no longer exist.
    $this->assertModuleTablesDoNotExist($module);
    // Check that the module's config files no longer exist.
    $this->assertNoModuleConfig($module);
  }

  /**
   * Verifies a module's help.
   *
   * Verifies that the module help page from hook_help() exists and can be
   * displayed, and that it contains the phrase "Foo Bar module", where "Foo
   * Bar" is the name of the module from the .info.yml file.
   *
   * @param string $module
   *   Machine name of the module to verify.
   * @param string $name
   *   Human-readable name of the module to verify.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  protected function assertHelp($module, $name): void {
    $this->drupalGet('admin/help/' . $module);
    // DO NOT change to $this->assertNoErrorMessage(), keep this as it is since
    // the page text may contain a warning message such as "There are no Cloud
    // Service Provider modules enabled. Please enable...".
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains($this->t('Error message'));
    $this->assertSession()->pageTextContains($name . ' module');
    $this->assertSession()->linkExists("online documentation for the ${name} module");
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

}
