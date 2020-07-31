<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the replica set view builders.
 */
class K8sReplicaSetViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'replica_set',
        'title' => $this->t('ReplicaSet'),
        'open' => TRUE,
        'fields' => [
          'name',
          'namespace',
          'replicas',
          'selector',
          'template',
          'labels',
          'annotations',
        ],
      ],
      [
        'name' => 'status',
        'title' => $this->t('Status'),
        'open' => TRUE,
        'fields' => [
          'conditions',
          'available_replicas',
          'fully_labeled_replicas',
          'ready_replicas',
          'observed_generation',
        ],
      ],
      [
        'name' => 'replica_set_detail',
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
