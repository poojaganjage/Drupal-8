<?php

namespace Drupal\cloud_budget\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the cloud credit entity type.
 */
class CloudCreditViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['cloud_credit']['cloud_credit_bulk_form'] = [
      'title' => $this->t('Credit operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Credits.'),
      'field' => [
        'id' => 'cloud_credit_bulk_form',
      ],
    ];

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
