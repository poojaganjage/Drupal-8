<?php

namespace Drupal\k8s\Entity;

/**
 * Provides the views data for the persistent volume entity type.
 */
class K8sPersistentVolumeViewsData extends K8sViewsDataBase {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['k8s_persistent_volume']['persistent_volume_bulk_form'] = [
      'title' => $this->t('Persistent Volume operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple persistent volume.'),
      'field' => [
        'id' => 'persistent_volume_bulk_form',
      ],
    ];

    return $data;
  }

}
