<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the persistent volume claim view builders.
 */
class K8sPersistentVolumeClaimViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'persistent_volume_claim',
        'title' => $this->t('Persistent Volume Claim'),
        'open' => TRUE,
        'fields' => [
          'name',
          'namespace',
          'phase',
          'volume_name',
          'capacity',
          'request',
          'access_mode',
          'storage_class',
          'created',
          'labels',
          'annotations',
        ],
      ],
      [
        'name' => 'persistent_volume_claim_detail',
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
