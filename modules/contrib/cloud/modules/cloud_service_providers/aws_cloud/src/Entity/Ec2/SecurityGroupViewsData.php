<?php

namespace Drupal\aws_cloud\Entity\Ec2;

/**
 * Provides the views data for the CloudScripting entity type.
 */
class SecurityGroupViewsData extends AwsCloudViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    $data['aws_cloud_security_group']['security_group_bulk_form'] = [
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
      'title' => $this->t('AWS Security Group'),
      'help'  => $this->t('The AWS Security Group entity'),
    ];

    $this->addDropdownSelector($data, $table_name, $fields, $selectable);

    return $data;
  }

}
