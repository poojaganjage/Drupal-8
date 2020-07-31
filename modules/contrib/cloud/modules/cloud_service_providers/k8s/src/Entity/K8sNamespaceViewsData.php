<?php

namespace Drupal\k8s\Entity;

/**
 * Provides the views data for the K8s Namespace entity type.
 */
class K8sNamespaceViewsData extends K8sViewsDataBase {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['k8s_namespace']['namespace_bulk_form'] = [
      'title' => $this->t('Namespace operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Namespaces.'),
      'field' => [
        'id' => 'namespace_bulk_form',
      ],
    ];

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
