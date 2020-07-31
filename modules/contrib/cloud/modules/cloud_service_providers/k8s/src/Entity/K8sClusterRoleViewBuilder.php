<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the cluster role view builders.
 */
class K8sClusterRoleViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'cluster_role',
        'title' => $this->t('Cluster Role'),
        'open' => TRUE,
        'fields' => [
          'name',
          'created',
          'labels',
          'annotations',
          'rules',
        ],
      ],
      [
        'name' => 'cluster_role_detail',
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
