<?php

namespace Drupal\k8s\Entity;

/**
 * Provides the views data for the Role Binding entity type.
 */
class K8sRoleBindingViewsData extends K8sViewsDataBase {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['k8s_role_binding']['role_binding_bulk_form'] = [
      'title' => $this->t('Role Binding operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Role Binding.'),
      'field' => [
        'id' => 'role_binding_bulk_form',
      ],
    ];

    return $data;
  }

}
