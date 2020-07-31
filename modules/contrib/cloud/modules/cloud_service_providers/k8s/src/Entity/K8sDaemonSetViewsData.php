<?php

namespace Drupal\k8s\Entity;

/**
 * Provides the views data for the Daemon Set entity type.
 */
class K8sDaemonSetViewsData extends K8sViewsDataBase {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['k8s_daemon_set']['daemon_set_bulk_form'] = [
      'title' => $this->t('Daemon Set operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Daemon Sets.'),
      'field' => [
        'id' => 'daemon_set_bulk_form',
      ],
    ];

    return $data;
  }

}
