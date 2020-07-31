<?php

namespace Drupal\Tests\cloud\Functional\Module;

/**
 * Install/uninstall module(s) and confirm table creation/deletion.
 *
 * @group Cloud
 */
class InstallUninstallTest extends CloudModuleTestBase {

  /**
   * Tests that a fixed set of modules can be installed and uninstalled.
   */
  public function testInstallUninstall(): void {
    $this->runInstallUninstall($this->getModuleName(__CLASS__));
  }

}
