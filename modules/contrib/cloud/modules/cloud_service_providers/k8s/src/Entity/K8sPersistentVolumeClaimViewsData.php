<?php

namespace Drupal\k8s\Entity;

/**
 * Provides the views data for the Persistent Volume Claim entity type.
 */
class K8sPersistentVolumeClaimViewsData extends K8sViewsDataBase {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['k8s_persistent_volume_claim']['persistent_volume_claim_bulk_form'] = [
      'title' => $this->t('Persistent Volume Claim operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Persistent Volume Claims.'),
      'field' => [
        'id' => 'persistent_volume_claim_bulk_form',
      ],
    ];

    return $data;
  }

}
