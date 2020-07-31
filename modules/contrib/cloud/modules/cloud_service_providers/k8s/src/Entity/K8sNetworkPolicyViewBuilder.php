<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the Network Policy view builders.
 */
class K8sNetworkPolicyViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'network_policy',
        'title' => $this->t('Network Policy'),
        'open' => TRUE,
        'fields' => [
          'name',
          'namespace',
          'created',
          'egress',
          'ingress',
          'pod_selector',
          'policy_types',
          'labels',
          'annotations',
        ],
      ],
      [
        'name' => 'network_policy_detail',
        'title' => $this->t('Detail'),
        'open' => FALSE,
        'fields' => [
          'detail',
          'creation_yaml',
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
