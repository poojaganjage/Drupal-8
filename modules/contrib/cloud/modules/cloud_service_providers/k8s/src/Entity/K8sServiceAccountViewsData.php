<?php

namespace Drupal\k8s\Entity;

/**
 * Provides the views data for the Service Account entity type.
 */
class K8sServiceAccountViewsData extends K8sViewsDataBase {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['k8s_service_account']['service_account_bulk_form'] = [
      'title' => $this->t('Service Account operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Service Accounts.'),
      'field' => [
        'id' => 'service_account_bulk_form',
      ],
    ];

    return $data;
  }

}
