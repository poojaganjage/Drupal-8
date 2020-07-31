<?php

namespace Drupal\k8s\Entity;

/**
 * Provides the views data for the Cluster Role Binding entity type.
 */
class K8sClusterRoleBindingViewsData extends K8sViewsDataBase {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['k8s_cluster_role_binding']['cluster_role_binding_bulk_form'] = [
      'title' => $this->t('Cluster Role Binding operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Cluster Roles Binding.'),
      'field' => [
        'id' => 'cluster_role_binding_bulk_form',
      ],
    ];

    return $data;
  }

}
