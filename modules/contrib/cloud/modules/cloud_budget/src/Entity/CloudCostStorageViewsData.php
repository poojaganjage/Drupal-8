<?php

namespace Drupal\cloud_budget\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the cloud cost storage entity type.
 */
class CloudCostStorageViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['cloud_cost_storage']['cloud_cost_storage_bulk_form'] = [
      'title' => $this->t('Cost storage operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Cost Storages.'),
      'field' => [
        'id' => 'cloud_cost_storage_bulk_form',
      ],
    ];

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
