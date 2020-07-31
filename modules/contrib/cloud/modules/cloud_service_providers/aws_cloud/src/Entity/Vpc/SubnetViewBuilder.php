<?php

namespace Drupal\aws_cloud\Entity\Vpc;

use Drupal\aws_cloud\Entity\Ec2\AwsCloudViewBuilder;

/**
 * Provides the subnet view builders.
 */
class SubnetViewBuilder extends AwsCloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'subnet',
        'title' => $this->t('Subnet'),
        'open' => TRUE,
        'fields' => [
          'subnet_id',
          'vpc_id',
          'state',
          'account_id',
          'created',
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
