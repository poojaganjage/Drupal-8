<?php

namespace Drupal\aws_cloud\Entity\Vpc;

use Drupal\aws_cloud\Entity\Ec2\AwsCloudViewsData;

/**
 * Provides the views data for the VPC entity type.
 */
class VpcPeeringConnectionViewsData extends AwsCloudViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['aws_cloud_vpc_peering_connection']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('AWS Cloud VPC Peering Connection'),
      'help'  => $this->t('The AWC Cloud VPC Peering Connection entity ID.'),
    ];

    $data['aws_cloud_vpc_peering_connection']['vpc_peering_connection_bulk_form'] = [
      'title' => $this->t('VPC peering connection operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple VPCs.'),
      'field' => [
        'id' => 'vpc_peering_connection_bulk_form',
      ],
    ];

    $table_name = $this->storage->getEntityTypeId();
    $fields = $this->getFieldStorageDefinitions($table_name);

    // The following is a list of fields to turn from text search to
    // select list.  This list can be expanded through hook_views_data_alter().
    $selectable = [
      'vpc_peering_connection_id',
      'status_code',
    ];

    $data['aws_cloud_vpc_peering_connection']['table']['base']['access query tag'] = 'aws_cloud_vpc_peering_connection_views_access';
    $this->addDropdownSelector($data, $table_name, $fields, $selectable);

    return $data;
  }

}
