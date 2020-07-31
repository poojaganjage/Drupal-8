<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the job view builders.
 */
class K8sJobViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'job',
        'title' => $this->t('Job'),
        'open' => TRUE,
        'fields' => [
          'name',
          'namespace',
          'image',
          'completions',
          'parallelism',
          'created',
          'labels',
          'annotations',
        ],
      ],
      [
        'name' => 'pod_status',
        'title' => $this->t('Pod Status'),
        'open' => TRUE,
        'fields' => [
          'active',
          'succeeded',
          'failed',
        ],
      ],
      [
        'name' => 'job_detail',
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
