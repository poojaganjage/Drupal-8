<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the ServiceAccount view builders.
 */
class K8sServiceAccountViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'service_account',
        'title' => $this->t('Service Account'),
        'open' => TRUE,
        'fields' => [
          'name',
          'namespace',
          'secrets',
          'image_pull_secrets',
          'created',
          'labels',
          'annotations',
        ],
      ],
      [
        'name' => 'service_account_detail',
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
