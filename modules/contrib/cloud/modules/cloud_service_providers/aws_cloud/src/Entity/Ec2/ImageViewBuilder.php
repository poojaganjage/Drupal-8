<?php

namespace Drupal\aws_cloud\Entity\Ec2;

/**
 * Provides the image view builders.
 */
class ImageViewBuilder extends AwsCloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'image',
        'title' => $this->t('Image'),
        'open' => TRUE,
        'fields' => [
          'name',
          'description',
          'ami_name',
          'image_id',
          'instance_id',
          'account_id',
          'source',
          'status',
          'state_reason',
          'created',
        ],
      ],
      [
        'name' => 'launch_permission_fieldset',
        'title' => $this->t('Launch Permission'),
        'open' => TRUE,
        'fields' => [
          'visibility',
          'launch_permission_account_ids',
        ],
      ],
      [
        'name' => 'type',
        'title' => $this->t('Type'),
        'open' => TRUE,
        'fields' => [
          'platform',
          'architecture',
          'virtualization_type',
          'product_code',
          'image_type',
        ],
      ],
      [
        'name' => 'device',
        'title' => $this->t('Device'),
        'open' => TRUE,
        'fields' => [
          'root_device_name',
          'root_device_type',
          'kernel_id',
          'ramdisk_id',
          'block_device_mappings',
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
