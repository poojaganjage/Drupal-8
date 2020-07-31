<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the stateful set view builders.
 */
class K8sStatefulSetViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'stateful_set',
        'title' => $this->t('Stateful Set'),
        'open' => TRUE,
        'fields' => [
          'name',
          'namespace',
          'pod_management_policy',
          'service_name',
          'revision_history_limit',
          'created',
          'labels',
          'annotations',
        ],
      ],
      [
        'name' => 'status',
        'title' => $this->t('Status'),
        'open' => TRUE,
        'fields' => [
          'observed_generation',
          'replicas',
          'ready_replicas',
          'current_replicas',
          'updated_replicas',
          'current_revision',
          'update_revision',
          'collision_count',
        ],
      ],
      [
        'name' => 'stateful_set_detail',
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
