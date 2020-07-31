<?php

namespace Drupal\Tests\terraform\Traits;

use Drupal\Component\Utility\Random;

/**
 * The trait creating mock data for terraform testing.
 */
trait TerraformTestMockTrait {

  /**
   * Update createWorkspace in mock data.
   *
   * @param array $workspace
   *   The workspace mock data.
   */
  protected function addWorkspaceMockData(array $workspace): void {
    $mock_data = $this->getMockDataFromConfig();
    $random = new Random();

    $create_workspace = [
      'id' => $random->name(8, TRUE),
      'attributes' => [
        'name' => $workspace['name'],
        'created-at' => date('Y/m/d H:i:s'),
        'latest-change-at' => date('Y/m/d H:i:s'),
        'auto-apply' => TRUE,
        'terraform-version' => 'latest',
        'vcs-repo-identifier' => $random->name(8, TRUE) . '/' . $random->name(8, TRUE),
        'locked' => TRUE,
        'working-directory' => '',
      ],
    ];

    $mock_data['createWorkspace'] = $create_workspace;
    $mock_data['describeWorkspaces'] = [$create_workspace];

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteWorkspace in mock data.
   *
   * @param array $workspace
   *   The workspace mock data.
   */
  protected function deleteWorkspaceMockData(array $workspace): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_workspace = [
      'metadata' => [
        'name' => $workspace['name'],
      ],
    ];
    $mock_data['deleteWorkspace'] = $delete_workspace;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createVariable in mock data.
   *
   * @param array $variable
   *   The variable mock data.
   */
  protected function addVariableMockData(array $variable): void {
    $mock_data = $this->getMockDataFromConfig();
    $random = new Random();
    $create_variable = [
      'id' => $variable['attribute_key'],
      'attributes' => [
        'created-at' => date('Y/m/d H:i:s'),
        'key' => $variable['attribute_key'],
        'value' => $random->name(8, TRUE),
        'description' => $random->name(16, TRUE),
        'description' => 'terraform',
        'sensitive' => TRUE,
        'hcl' => TRUE,
      ],
    ];

    $mock_data['createVariable'] = $create_variable;
    $mock_data['describeVariables'] = [$create_variable];

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteVariable in mock data.
   *
   * @param array $variable
   *   The variable mock data.
   */
  protected function deleteVariableMockData(array $variable): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_variable = [];
    $mock_data['deleteVariable'] = $delete_variable;
    $this->updateMockDataToConfig($mock_data);
  }

}
