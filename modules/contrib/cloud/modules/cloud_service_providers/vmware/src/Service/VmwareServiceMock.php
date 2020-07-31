<?php

namespace Drupal\vmware\Service;

use Drupal\Core\Form\ConfigFormBaseTrait;

/**
 * VmwareServiceMock service interacts with the Vmware API.
 */
class VmwareServiceMock extends VmwareService {

  use ConfigFormBaseTrait;

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
    return json_decode($this->config('vmware.settings')->get('vmware_mock_data'), TRUE)[$method_name] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['vmware.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function login() {
    return $this->getMockData(__FUNCTION__);
  }

}
