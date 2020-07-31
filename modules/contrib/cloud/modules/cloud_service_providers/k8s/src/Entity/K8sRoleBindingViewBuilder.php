<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the role binding view builders.
 */
class K8sRoleBindingViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'role_binding',
        'title' => $this->t('Role Binding'),
        'open' => TRUE,
        'fields' => [
          'name',
          'namespace',
          'subjects',
          'role_ref',
          'created',
          'labels',
          'annotations',
        ],
      ],
      [
        'name' => 'role_binding_detail',
        'title' => $this->t('Detail'),
        'open' => FALSE,
        'fields' => [
          'detail',
        ],
      ],
      [
        'name' => 'others',
        'title' => $this->t('Others'),
        'open' => FALSE,
        'fields' => [
          'cloud_context',
          'uid',
        ],
      ],
    ];
  }

}
