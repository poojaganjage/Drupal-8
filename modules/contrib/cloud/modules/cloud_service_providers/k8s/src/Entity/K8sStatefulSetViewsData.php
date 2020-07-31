<?php

namespace Drupal\k8s\Entity;

/**
 * Provides the views data for the Stateful Set entity type.
 */
class K8sStatefulSetViewsData extends K8sViewsDataBase {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['k8s_stateful_set']['stateful_set_bulk_form'] = [
      'title' => $this->t('Stateful Set operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Stateful Sets.'),
      'field' => [
        'id' => 'stateful_set_bulk_form',
      ],
    ];

    return $data;
  }

}
