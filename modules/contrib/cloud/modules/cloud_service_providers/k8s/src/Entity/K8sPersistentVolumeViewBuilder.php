<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the persistent volume view builders.
 */
class K8sPersistentVolumeViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'persistent_volume',
        'title' => $this->t('persistent volume'),
        'open' => TRUE,
        'fields' => [
          'name',
          'created',
          'labels',
          'annotations',
          'capacity',
          'access_modes',
          'reclaim_policy',
          'storage_class_name',
          'claim_ref',
        ],
      ],
      [
        'name' => 'status',
        'title' => $this->t('Status'),
        'open' => TRUE,
        'fields' => [
          'phase',
          'reason',
        ],
      ],
      [
        'name' => 'persistent_volume_detail',
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
