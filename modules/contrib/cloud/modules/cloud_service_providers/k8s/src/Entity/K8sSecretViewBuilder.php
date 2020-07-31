<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the secret view builders.
 */
class K8sSecretViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'secret',
        'title' => $this->t('Secret'),
        'open' => TRUE,
        'fields' => [
          'name',
          'namespace',
          'created',
          'labels',
          'annotations',
          'secret_type',
        ],
      ],
      [
        'name' => 'secret_data',
        'title' => $this->t('Data'),
        'open' => TRUE,
        'fields' => [
          'data',
        ],
      ],
      [
        'name' => 'secret_detail',
        'title' => $this->t('Detail'),
        'open' => FALSE,
        'fields' => [
          'detail',
          'creation_yaml',
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
