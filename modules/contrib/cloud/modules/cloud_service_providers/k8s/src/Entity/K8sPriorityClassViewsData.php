<?php

namespace Drupal\k8s\Entity;

/**
 * Provides the views data for the Priority Class entity type.
 */
class K8sPriorityClassViewsData extends K8sViewsDataBase {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['k8s_priority_class']['priority_class_bulk_form'] = [
      'title' => $this->t('Priority Class operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Priority Classes.'),
      'field' => [
        'id' => 'priority_class_bulk_form',
      ],
    ];

    return $data;
  }

}
