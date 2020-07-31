<?php

namespace Drupal\k8s\Entity;

/**
 * Provides the views data for the ConfigMap entity type.
 */
class K8sConfigMapViewsData extends K8sViewsDataBase {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['k8s_config_map']['config_map_bulk_form'] = [
      'title' => $this->t('ConfigMap operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple ConfigMaps.'),
      'field' => [
        'id' => 'config_map_bulk_form',
      ],
    ];

    // Additional information for Views integration, such as table joins, can be
    // put here.
    $data['k8s_config_map']['table']['base']['access query tag'] = 'k8s_entity_views_access_with_namespace';
    $data['k8s_config_map']['table']['base']['query metadata'] = ['base_table' => 'k8s_config_map'];

    return $data;
  }

}
