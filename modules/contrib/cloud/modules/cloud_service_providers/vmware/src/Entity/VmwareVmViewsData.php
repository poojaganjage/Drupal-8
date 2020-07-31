<?php

namespace Drupal\vmware\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the VMware VM entity type.
 */
class VmwareVmViewsData extends EntityViewsData {

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
