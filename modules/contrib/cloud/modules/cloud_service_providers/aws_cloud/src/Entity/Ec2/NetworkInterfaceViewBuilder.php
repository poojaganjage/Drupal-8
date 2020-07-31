<?php

namespace Drupal\aws_cloud\Entity\Ec2;

/**
 * Provides the network interface view builders.
 */
class NetworkInterfaceViewBuilder extends AwsCloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'network_interface',
        'title' => $this->t('Network Interface'),
        'open' => TRUE,
        'fields' => [
          'description',
          'network_interface_id',
          'instance_id',
          'allocation_id',
          'mac_address',
          'device_index',
          'status',
          'delete_on_termination',
          'created',
        ],
      ],
      [
        'name' => 'network',
        'title' => $this->t('Network'),
        'open' => TRUE,
        'fields' => [
          'security_groups',
          'vpc_id',
          'subnet_id',
          'public_ips',
          'primary_private_ip',
          'secondary_private_ips',
          'private_dns',
        ],
      ],
      [
        'name' => 'attachment',
        'title' => $this->t('Attachment'),
        'open' => FALSE,
        'fields' => [
          'attachment_id',
          'attachment_owner',
          'attachment_status',
        ],
      ],
      [
        'name' => 'owner',
        'title' => $this->t('Owner'),
        'open' => FALSE,
        'fields' => ['account_id'],
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
