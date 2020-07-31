<?php

namespace Drupal\k8s\Entity;

/**
 * Provides the views data for the API service entity type.
 */
class K8sApiServiceViewsData extends K8sViewsDataBase {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['k8s_api_service']['api_service_bulk_form'] = [
      'title' => $this->t('API Service operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API service.'),
      'field' => [
        'id' => 'api_service_bulk_form',
      ],
    ];

    return $data;
  }

}
