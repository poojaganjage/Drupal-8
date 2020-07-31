<?php

namespace Drupal\k8s\Entity;

/**
 * Provides the views data for the K8s Event entity type.
 */
class K8sEventViewsData extends K8sViewsDataBase {

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
