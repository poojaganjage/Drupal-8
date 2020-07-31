<?php

namespace Drupal\k8s\Entity;

/**
 * Provides the views data for the Cluster Role entity type.
 */
class K8sClusterRoleViewsData extends K8sViewsDataBase {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['k8s_cluster_role']['cluster_role_bulk_form'] = [
      'title' => $this->t('Cluster Role operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Cluster Roles.'),
      'field' => [
        'id' => 'cluster_role_bulk_form',
      ],
    ];

    return $data;
  }

}
