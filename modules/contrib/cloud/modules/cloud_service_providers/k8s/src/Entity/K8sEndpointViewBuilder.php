<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the Endpoint view builders.
 */
class K8sEndpointViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'endpoint',
        'title' => $this->t('Endpoint'),
        'open' => TRUE,
        'fields' => [
          'name',
          'namespace',
          'node_name',
          'addresses',
        ],
      ],
      [
        'name' => 'endpoint_detail',
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
