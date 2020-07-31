<?php

namespace Drupal\k8s\Entity;

/**
 * Provides the views data for the Resource Quota entity type.
 */
class K8sResourceQuotaViewsData extends K8sViewsDataBase {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['k8s_resource_quota']['resource_quota_bulk_form'] = [
      'title' => $this->t('Resource Quota operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Resource Quotas.'),
      'field' => [
        'id' => 'resource_quota_bulk_form',
      ],
    ];

    // Additional information for Views integration, such as table joins, can be
    // put here.
    $data['k8s_resource_quota']['table']['base']['access query tag'] = 'k8s_entity_views_access_with_namespace';
    $data['k8s_resource_quota']['table']['base']['query metadata'] = ['base_table' => 'k8s_resource_quota'];

    return $data;
  }

}
