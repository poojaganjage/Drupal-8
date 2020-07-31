<?php

namespace Drupal\aws_cloud\Entity\Vpc;

use Drupal\aws_cloud\Entity\Ec2\AwsCloudViewBuilder;

/**
 * Provides the VPC Peering Connection view builders.
 */
class VpcPeeringConnectionViewBuilder extends AwsCloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'vpc_peering_connection',
        'title' => $this->t('VPC Peering Connection'),
        'open' => TRUE,
        'fields' => [
          'vpc_peering_connection_id',
          'status_code',
          'status_message',
          'expiration_time',
          'created',
        ],
      ],
      [
        'name' => 'requester',
        'title' => $this->t('Requester'),
        'open' => TRUE,
        'fields' => [
          'requester_vpc_id',
          'requester_cidr_block',
          'requester_account_id',
          'requester_region',
        ],
      ],
      [
        'name' => 'accepter',
        'title' => $this->t('Accepter'),
        'open' => TRUE,
        'fields' => [
          'accepter_vpc_id',
          'accepter_cidr_block',
          'accepter_account_id',
          'accepter_region',
        ],
      ],
      [
        'name' => 'fieldset_tags',
        'title' => $this->t('Tags'),
        'open' => TRUE,
        'fields' => [
          'tags',
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
