<?php

namespace Drupal\k8s\Entity;

use Drupal\views\EntityViewsData;

/**
 * Defines the base of K8s views data.
 */
class K8sViewsDataBase extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $base_table = $this->entityType->getBaseTable() ?: $this->entityType->id();

    if (isset($data[$base_table]['cloud_context'])) {
      $data[$base_table]['cloud_context']['relationship'] = [
        'title' => t('K8s Project'),
        'label'  => $this->t('K8s Project'),
        'help' => t('Relate k8s project to k8s resources.'),
        'id' => 'k8s_cloud_project',
        'label' => t('k8s_cloud_project'),
        'base' => 'cloud_project_field_data',
        'base field' => 'id',
      ];
    }

    $data[$base_table]['cloud_config']['relationship'] = [
      'id' => 'standard',
      'title' => t('Cloud Config'),
      'label' => t('Cloud Config'),
      'group' => 'Kubernetes Pod',
      'help' => t('Reference to cloud config'),
      'base' => 'cloud_config_field_data',
      'base field' => 'cloud_context',
      'relationship field' => 'cloud_context',
    ];
    return $data;
  }

}
