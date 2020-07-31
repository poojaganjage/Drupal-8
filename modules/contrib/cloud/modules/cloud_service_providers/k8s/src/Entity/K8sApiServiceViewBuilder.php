<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the API service view builders.
 */
class K8sApiServiceViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'api_service',
        'title' => $this->t('API Service'),
        'open' => TRUE,
        'fields' => [
          'name',
          'created',
          'labels',
          'group_priority_minimum',
          'service',
          'version_priority',
          'group',
          'insecure_skip_tls_verify',
          'version',
        ],
      ],
      [
        'name' => 'status',
        'title' => $this->t('Status'),
        'open' => TRUE,
        'fields' => [
          'conditions',
        ],
      ],
      [
        'name' => 'api_service_detail',
        'title' => $this->t('Detail'),
        'open' => FALSE,
        'fields' => [
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
