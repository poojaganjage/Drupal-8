<?php

namespace Drupal\aws_cloud\Entity\Vpc;

use Drupal\aws_cloud\Entity\Ec2\AwsCloudViewBuilder;

/**
 * Provides the VPC view builders.
 */
class VpcViewBuilder extends AwsCloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'vpc',
        'title' => $this->t('VPC'),
        'open' => TRUE,
        'fields' => [
          'vpc_id',
          'state',
          'dhcp_options_id',
          'instance_tenancy',
          'is_default',
          'account_id',
          'created',
        ],
      ],
      [
        'name' => 'fieldset_cidr_blocks',
        'title' => $this->t('CIDR Blocks'),
        'open' => TRUE,
        'fields' => [
          'cidr_blocks',
        ],
      ],
      [
        'name' => 'fieldset_ipv6_cidr_blocks',
        'title' => $this->t('IPv6 CIDR Blocks'),
        'open' => TRUE,
        'fields' => [
          'ipv6_cidr_blocks',
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
