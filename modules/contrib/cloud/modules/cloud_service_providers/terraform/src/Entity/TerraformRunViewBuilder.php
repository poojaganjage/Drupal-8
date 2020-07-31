<?php

namespace Drupal\terraform\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides the Run view builders.
 */
class TerraformRunViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'run',
        'title' => $this->t('Run'),
        'open' => TRUE,
        'fields' => [
          'name',
          'status',
          'message',
          'source',
          'trigger_reason',
          'created',
        ],
      ],
      [
        'name' => 'plan',
        'title' => $this->t('Plan'),
        'open' => TRUE,
        'fields' => [
          'plan_id',
          'plan_log',
        ],
      ],
      [
        'name' => 'apply',
        'title' => $this->t('Apply'),
        'open' => TRUE,
        'fields' => [
          'apply_id',
          'apply_log',
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

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $build = parent::view($entity, $view_mode, $langcode);

    if (empty($entity->get('apply_log')->value)) {
      $build['apply']['#open'] = FALSE;
    }
    else {
      $build['plan']['#open'] = FALSE;
    }

    return $build;
  }

}
