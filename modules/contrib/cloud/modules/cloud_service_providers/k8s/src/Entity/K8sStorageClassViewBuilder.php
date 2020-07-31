<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the storage class view builders.
 */
class K8sStorageClassViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'storage_class',
        'title' => $this->t('Storage Class'),
        'open' => TRUE,
        'fields' => [
          'name',
          'created',
          'labels',
          'annotations',
          'parameters',
          'provisioner',
          'reclaim_policy',
          'volume_binding_mode',
        ],
      ],
      [
        'name' => 'storage_class_detail',
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
