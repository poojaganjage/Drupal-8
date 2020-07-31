<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the service view builders.
 */
class K8sServiceViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'service',
        'title' => $this->t('Service'),
        'open' => TRUE,
        'fields' => [
          'name',
          'namespace',
          'type',
          'session_affinity',
          'cluster_ip',
          'internal_endpoints',
          'external_endpoints',
          'created',
          'labels',
          'annotations',
          'selector',
        ],
      ],
      [
        'name' => 'service_detail',
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
