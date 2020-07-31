<?php

namespace Drupal\aws_cloud\Entity\Ec2;

/**
 * Provides the Elastic IP view builders.
 */
class ElasticIpViewBuilder extends AwsCloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'ip_address',
        'title' => $this->t('IP Address'),
        'open' => TRUE,
        'fields' => [
          'public_ip',
          'private_ip_address',
          'created',
        ],
      ],
      [
        'name' => 'assign',
        'title' => $this->t('Assign'),
        'open' => TRUE,
        'fields' => [
          'instance_id',
          'network_interface_id',
          'allocation_id',
          'association_id',
          'domain',
          'network_interface_owner',
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
