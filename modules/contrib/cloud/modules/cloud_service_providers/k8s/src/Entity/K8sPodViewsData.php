<?php

namespace Drupal\k8s\Entity;

/**
 * Provides the views data for the Pod entity type.
 */
class K8sPodViewsData extends K8sViewsDataBase {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['k8s_pod']['pod_bulk_form'] = [
      'title' => $this->t('Pod operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Pods.'),
      'field' => [
        'id' => 'pod_bulk_form',
      ],
    ];

    // Additional information for Views integration, such as table joins, can be
    // put here.
    $data['k8s_pod']['table']['base']['access query tag'] = 'k8s_entity_views_access_with_namespace';
    $data['k8s_pod']['table']['base']['query metadata'] = ['base_table' => 'k8s_pod'];

    $data['k8s_pod']['node_name']['argument'] = [
      'id' => 'k8s_node_id',
    ];

    return $data;
  }

}
