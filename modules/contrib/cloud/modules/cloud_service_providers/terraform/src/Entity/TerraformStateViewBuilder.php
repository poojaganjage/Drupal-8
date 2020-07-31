<?php

namespace Drupal\terraform\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the State view builders.
 */
class TerraformStateViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'state',
        'title' => $this->t('State'),
        'open' => TRUE,
        'fields' => [
          'name',
          'run_id',
          'serial_no',
          'created',
          'detail',
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
