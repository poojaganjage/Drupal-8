<?php

namespace Drupal\Tests\aws_cloud\Functional\Module;

use Drupal\Tests\cloud\Functional\Module\CloudModuleTestBase;

/**
 * Tests install/uninstall module(s) and confirm table creation/deletion.
 *
 * @group AWS Cloud
 */
class InstallUninstallTest extends CloudModuleTestBase {

  /**
   * Tests that a fixed set of modules can be installed and uninstalled.
   */
  public function testInstallUninstall(): void {
    $this->runInstallUninstall($this->getModuleName(__CLASS__));
  }

}
