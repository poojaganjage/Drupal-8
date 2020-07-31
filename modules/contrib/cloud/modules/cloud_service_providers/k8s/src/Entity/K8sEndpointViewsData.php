<?php

namespace Drupal\k8s\Entity;

/**
 * Provides the views data for the Endpoint entity type.
 */
class K8sEndpointViewsData extends K8sViewsDataBase {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['k8s_endpoint']['endpoint_bulk_form'] = [
      'title' => $this->t('Endpoint operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Endpoints.'),
      'field' => [
        'id' => 'endpoint_bulk_form',
      ],
    ];

    $data['k8s_endpoint']['node_name']['argument'] = [
      'id' => 'k8s_node_id',
    ];

    return $data;
  }

}
