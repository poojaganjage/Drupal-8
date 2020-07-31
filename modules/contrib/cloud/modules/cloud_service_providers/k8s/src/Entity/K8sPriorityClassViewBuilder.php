<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the priority class view builders.
 */
class K8sPriorityClassViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'priority_class',
        'title' => $this->t('Priority class'),
        'open' => TRUE,
        'fields' => [
          'name',
          'created',
          'labels',
          'annotations',
          'value',
          'global_default',
          'description',
        ],
      ],
      [
        'name' => 'priority_class_detail',
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
