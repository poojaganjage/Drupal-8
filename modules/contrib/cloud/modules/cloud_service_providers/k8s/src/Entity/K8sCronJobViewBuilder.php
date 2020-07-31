<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the cron job view builders.
 */
class K8sCronJobViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'cron_job',
        'title' => $this->t('Cron Job'),
        'open' => TRUE,
        'fields' => [
          'name',
          'namespace',
          'schedule',
          'active',
          'suspend',
          'last_schedule_time',
          'concurrency_policy',
          'created',
          'labels',
          'annotations',
        ],
      ],
      [
        'name' => 'cron_job_detail',
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
