<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the ConfigNap view builders.
 */
class K8sConfigMapViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'config_map',
        'title' => $this->t('ConfigMap'),
        'open' => TRUE,
        'fields' => [
          'name',
          'namespace',
          'created',
          'labels',
          'annotations',
        ],
      ],
      [
        'name' => 'config_map_data',
        'title' => $this->t('Data'),
        'open' => TRUE,
        'fields' => [
          'data',
        ],
      ],
      [
        'name' => 'config_map_detail',
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
