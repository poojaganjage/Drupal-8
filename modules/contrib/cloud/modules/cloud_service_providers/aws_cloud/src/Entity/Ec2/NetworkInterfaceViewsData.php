<?php

namespace Drupal\aws_cloud\Entity\Ec2;

/**
 * Provides the views data for the NetworkInterface entity type.
 */
class NetworkInterfaceViewsData extends AwsCloudViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['aws_cloud_network_interface']['network_interface_bulk_form'] = [
      'title' => $this->t('Network Interface operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Network Interfaces.'),
      'field' => [
        'id' => 'network_interface_bulk_form',
      ],
    ];

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
