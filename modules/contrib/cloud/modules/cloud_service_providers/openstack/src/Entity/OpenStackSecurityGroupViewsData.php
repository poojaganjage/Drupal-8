<?php

namespace Drupal\openstack\Entity;

use Drupal\aws_cloud\Entity\Ec2\AwsCloudViewsData;

/**
 * Provides the views data for the CloudScripting entity type.
 */
class OpenStackSecurityGroupViewsData extends AwsCloudViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    $data['openstack_security_group']['security_group_bulk_form'] = [
      'title' => $this->t('Security Group operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Security Groups.'),
      'field' => [
        'id' => 'security_group_bulk_form',
      ],
    ];
    $table_name = $this->storage->getEntityTypeId();
    $fields = $this->getFieldStorageDefinitions($table_name);

    // The following is a list of fields to turn from text search to
    // select list.  This list can be expanded through hook_views_data_alter().
    $selectable = [
      'vpc_id',
    ];

    $data['$table_name']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('OpenStack Security Group'),
      'help'  => $this->t('The OpenStack Security Group entity'),
    ];

    $this->addDropdownSelector($data, $table_name, $fields, $selectable);

    return $data;
  }

}
