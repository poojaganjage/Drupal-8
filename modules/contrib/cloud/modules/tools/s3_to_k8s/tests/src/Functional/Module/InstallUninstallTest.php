<?php

namespace Drupal\Tests\s3_to_k8s\Functional\Module;

use Drupal\Tests\cloud\Functional\Module\CloudModuleTestBase;

/**
 * Install/uninstall module(s) and confirm table creation/deletion.
 *
 * @group K8s
 */
class InstallUninstallTest extends CloudModuleTestBase {

  /**
   * Tests that a fixed set of modules can be installed and uninstalled.
   */
  public function testInstallUninstall(): void {
    $this->runInstallUninstall($this->getModuleName(__CLASS__));
  }

}
