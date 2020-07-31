<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the daemon set view builders.
 */
class K8sDaemonSetViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'daemon_set',
        'title' => $this->t('Daemon Set'),
        'open' => TRUE,
        'fields' => [
          'name',
          'namespace',
          'cpu_request',
          'cpu_limit',
          'memory_request',
          'memory_limit',
          'created',
          'labels',
          'annotations',
        ],
      ],
      [
        'name' => 'daemon_set_detail',
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
