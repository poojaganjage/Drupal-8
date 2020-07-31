<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the pod view builders.
 */
class K8sPodViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'entity_metrics',
        'title' => $this->t('Metrics'),
        'open' => TRUE,
        'fields' => [],
      ],
      [
        'name' => 'pod',
        'title' => $this->t('Pod'),
        'open' => TRUE,
        'fields' => [
          'name',
          'namespace',
          'status',
          'qos_class',
          'node_name',
          'pod_ip',
          'created',
          'labels',
          'annotations',
        ],
      ],
      [
        'name' => 'metrics',
        'title' => $this->t('Metrics'),
        'open' => TRUE,
        'fields' => [
          'cpu_request',
          'cpu_limit',
          'cpu_usage',
          'memory_request',
          'memory_limit',
          'memory_usage',
        ],
      ],
      [
        'name' => 'pod_containers',
        'title' => $this->t('Containers'),
        'open' => FALSE,
        'fields' => [
          'containers',
        ],
      ],
      [
        'name' => 'pod_detail',
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
