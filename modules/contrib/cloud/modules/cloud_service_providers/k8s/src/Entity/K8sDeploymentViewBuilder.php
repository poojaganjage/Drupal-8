<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the deployment view builders.
 */
class K8sDeploymentViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'deployment',
        'title' => $this->t('Deployment'),
        'open' => TRUE,
        'fields' => [
          'name',
          'namespace',
          'strategy',
          'min_ready_seconds',
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
          'available_replicas',
          'collision_count',
          'observed_generation',
          'ready_replicas',
          'replicas',
          'unavailable_replicas',
          'updated_replicas',
        ],
      ],
      [
        'name' => 'deployment_detail',
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
