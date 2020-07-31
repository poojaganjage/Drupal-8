<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the event view builders.
 */
class K8sEventViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'event',
        'title' => $this->t('Event'),
        'open' => TRUE,
        'fields' => [
          'type',
          'reason',
          'object_kind',
          'object_name',
          'message',
          'time_stamp',
        ],
      ],
      [
        'name' => 'event_detail',
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
