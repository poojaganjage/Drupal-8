<?php

namespace Drupal\cloud\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for cloud server template entities.
 */
class CloudServerTemplateViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
