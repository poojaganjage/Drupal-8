<?php

namespace Drupal\openstack\Entity;

use Drupal\aws_cloud\Entity\Ec2\AwsCloudViewsData;

/**
 * Provides the views data for the CloudScripting entity type.
 */
class OpenStackImageViewsData extends AwsCloudViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['openstack_image']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('OpenStack Image'),
      'help'  => $this->t('The OpenStack Image entity ID.'),
    ];

    $data['openstack_image']['image_bulk_form'] = [
      'title' => $this->t('Image operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple images.'),
      'field' => [
        'id' => 'image_bulk_form',
      ],
    ];

    $table_name = $this->storage->getEntityTypeId();
    $fields = $this->getFieldStorageDefinitions($table_name);

    // The following is a list of fields to turn from text search to
    // select list.  This list can be expanded through hook_views_data_alter().
    $selectable = [
      'image_id',
      'source',
      'architecture',
      'virtualization_type',
      'image_type',
      'root_device_type',
      'kernel_id',
      'account_id',
      'visibility',
    ];

    $data['openstack_image']['table']['base']['access query tag'] = 'openstack_image_views_access';
    $this->addDropdownSelector($data, $table_name, $fields, $selectable);

    return $data;
  }

}
