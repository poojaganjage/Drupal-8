<?php

namespace Drupal\terraform\Service;

use Drupal\Core\Form\ConfigFormBaseTrait;

/**
 * TerraformServiceMock service interacts with the Terraform API.
 */
class TerraformServiceMock extends TerraformService {

  use ConfigFormBaseTrait;

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['terraform.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function describeWorkspaces(array $params = []) {
    return $this->getMockData(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function createWorkspace(array $params) {
    return $this->getMockData(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteWorkspace($name) {
    return $this->getMockData(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function describeRuns(array $params = []) {
    return $this->getMockData(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function describeStates(array $params = []) {
    return $this->getMockData(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function createVariable(array $params) {
    return $this->getMockData(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteVariable($name) {
    return $this->getMockData(__FUNCTION__);
  }

  /**
   * Get mock data for a method.
   *
   * @param string $method_name
   *   The method name.
   *
   * @return array
   *   An array of the mock data for a method.
   */
  private function getMockData($method_name) {
    return json_decode($this->config('terraform.settings')->get('terraform_mock_data'), TRUE)[$method_name] ?? [];
  }

}
