<?php

namespace Drupal\Tests\terraform\Traits;

use Drupal\Component\Utility\Random;

/**
 * The trait creating form data for terraform testing.
 */
trait TerraformTestFormDataTrait {

  /**
   * Create test data for cloud service provider (CloudConfig).
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createCloudConfigTestFormData($repeat_count): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0, $num = 1; $i < $repeat_count; $i++, $num++) {

      $data[] = [
        'name[0][value]'               => sprintf('config-entity-#%d-%s - %s', $num, $random->name(8, TRUE), date('Y/m/d H:i:s')),
        'cloud_context'                => strtolower($random->name(16, TRUE)),
        'field_organization[0][value]' => $random->name(32, TRUE),
        'field_api_token[0][value]'    => $random->name(128, TRUE),
      ];
    }

    return $data;
  }

  /**
   * Create test data for terraform workspace.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createWorkspaceTestFormData($repeat_count): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $data[] = [
        'name' => sprintf('Workspace-%s - %s', $random->name(8, TRUE), date('Y/m/d H:i:s')),
        'vcs_repo_identifier' => $random->name(16, TRUE),
        'oauth_token_id' => $random->name(8, TRUE),
      ];
    }

    return $data;
  }

  /**
   * Create random workspaces data.
   *
   * @return array
   *   Random workspaces.
   *
   * @throws \Exception
   */
  protected function createWorkspacesRandomTestFormData(): array {
    $workspaces = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $workspaces[] = [
        // 'cloud_context' needs to associate a fixed $cloud_context since the
        // workspace entities belong to a specific $cloud_context.
        // The Terraform organization is equal to a cloud service provider
        // (CloudConfig), which is $cloud_context here.
        'cloud_context' => $this->cloudContext,
        'name' => sprintf('workspace #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(4, TRUE)),
      ];
    }

    return $workspaces;
  }

  /**
   * Create test data for terraform variable.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createVariableTestFormData($repeat_count): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $data[] = [
        'attribute_key' => sprintf('Variable-%s - %s', $random->name(8, TRUE), date('Y/m/d H:i:s')),
        'attribute_value' => $random->name(8, TRUE),
      ];
    }

    return $data;
  }

  /**
   * Create random variable data.
   *
   * @return array
   *   Random variables.
   *
   * @throws \Exception
   */
  protected function createVariablesRandomTestFormData(): array {
    $variables = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $variables[] = [
        // 'cloud_context' needs to associate a fixed $cloud_context since the
        // variable entities belong to a specific $cloud_context.
        // The Terraform organization is equal to a cloud service provider
        // (CloudConfig), which is $cloud_context here.
        'cloud_context' => $this->cloudContext,
        'name' => sprintf('workspace #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(4, TRUE)),
        'terraform_workspace_id' => 1,
      ];
    }

    return $variables;
  }

}
