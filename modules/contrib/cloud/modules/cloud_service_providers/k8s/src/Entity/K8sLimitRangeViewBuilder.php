<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the limit range view builders.
 */
class K8sLimitRangeViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'limit_range',
        'title' => $this->t('Limit Range'),
        'open' => TRUE,
        'fields' => [
          'name',
          'namespace',
          'created',
          'labels',
          'annotations',
          'limits',
        ],
      ],
      [
        'name' => 'limit_range_detail',
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
