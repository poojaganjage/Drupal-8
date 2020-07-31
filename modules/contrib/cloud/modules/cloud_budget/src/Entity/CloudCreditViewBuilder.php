<?php

namespace Drupal\cloud_budget\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the cloud credit view builders.
 */
class CloudCreditViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'credit',
        'title' => $this->t('Credit'),
        'open' => TRUE,
        'fields' => [
          'user',
          'amount',
          'refreshed',
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
