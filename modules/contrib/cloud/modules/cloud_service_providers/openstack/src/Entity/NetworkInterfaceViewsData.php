<?php

namespace Drupal\openstack\Entity;

use Drupal\aws_cloud\Entity\Ec2\AwsCloudViewsData;

/**
 * Provides the views data for the NetworkInterface entity type.
 */
class NetworkInterfaceViewsData extends AwsCloudViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $table_name = $this->storage->getEntityTypeId();
    $fields = $this->getFieldStorageDefinitions($table_name);

    // The following is a list of fields to turn from text search to
    // select list.  This list can be expanded through hook_views_data_alter().
    $selectable = [
      'vpc_id',
      'status',
      'security_groups',
      'attachment_id',
      'attachment_owner',
      'attachment_status',
      'account_id',
      'association_id',
      'subnet_id',
      'availability_zone',
      'allocation_id',
    ];

    $this->addDropdownSelector($data, $table_name, $fields, $selectable);

    return $data;
  }

}
