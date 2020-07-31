<?php

namespace Drupal\vmware\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the VM view builders.
 */
class VmwareVmViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'vm',
        'title' => $this->t('VM'),
        'open' => TRUE,
        'fields' => [
          'name',
          'power_state',
          'cpu_count',
          'memory_size',
          'created',
        ],
      ],
      [
        'name' => 'others',
        'title' => $this->t('Others'),
        'open' => FALSE,
        'fields' => [
          'cloud_context',
          'uid',
        ],
      ],
    ];
  }

}
