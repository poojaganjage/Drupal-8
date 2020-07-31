<?php

namespace Drupal\cloud_budget\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the cloud cost storage view builders.
 */
class CloudCostStorageViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'cost',
        'title' => $this->t('Cost'),
        'open' => TRUE,
        'fields' => [
          'group',
          'cost',
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
