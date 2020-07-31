<?php

namespace Drupal\k8s\Entity;

/**
 * Provides the views data for the Limit Range entity type.
 */
class K8sLimitRangeViewsData extends K8sViewsDataBase {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['k8s_limit_range']['limit_range_bulk_form'] = [
      'title' => $this->t('Limit Range operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Limit Ranges.'),
      'field' => [
        'id' => 'limit_range_bulk_form',
      ],
    ];

    // Additional information for Views integration, such as table joins, can be
    // put here.
    $data['k8s_limit_range']['table']['base']['access query tag'] = 'k8s_entity_views_access_with_namespace';
    $data['k8s_limit_range']['table']['base']['query metadata'] = ['base_table' => 'k8s_limit_range'];

    return $data;
  }

}
