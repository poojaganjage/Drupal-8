<?php

namespace Drupal\openstack\Entity;

use Drupal\aws_cloud\Entity\Ec2\VolumeViewBuilder;

/**
 * Provides the volume view builders.
 */
class OpenStackVolumeViewBuilder extends VolumeViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'volume',
        'title' => $this->t('Volume'),
        'open' => TRUE,
        'fields' => [
          'attachment_information',
          'volume_id',
          'snapshot_id',
          'snapshot_name',
          'size',
          'availability_zone',
          'volume_type',
          'state',
          'created',
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
