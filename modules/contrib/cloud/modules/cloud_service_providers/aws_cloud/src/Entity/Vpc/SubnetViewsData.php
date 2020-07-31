<?php

namespace Drupal\aws_cloud\Entity\Vpc;

use Drupal\aws_cloud\Entity\Ec2\AwsCloudViewsData;

/**
 * Provides the views data for the Subnet entity type.
 */
class SubnetViewsData extends AwsCloudViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['aws_cloud_subnet']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('AWS Cloud Subnet'),
      'help'  => $this->t('The AWC Cloud Subnet entity ID.'),
    ];

    $data['aws_cloud_subnet']['subnet_bulk_form'] = [
      'title' => $this->t('Subnet operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Subnets.'),
      'field' => [
        'id' => 'subnet_bulk_form',
      ],
    ];

    $table_name = $this->storage->getEntityTypeId();
    $fields = $this->getFieldStorageDefinitions($table_name);

    // The following is a list of fields to turn from text search to
    // select list.  This list can be expanded through hook_views_data_alter().
    $selectable = [
      'subnet_id',
      'vpc_id',
      'state',
      'account_id',
    ];

    $data['aws_cloud_vpc']['table']['base']['access query tag'] = 'aws_cloud_subnet_views_access';
    $this->addDropdownSelector($data, $table_name, $fields, $selectable);

    return $data;
  }

}
