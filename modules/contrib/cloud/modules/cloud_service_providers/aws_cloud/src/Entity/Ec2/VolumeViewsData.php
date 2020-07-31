<?php

namespace Drupal\aws_cloud\Entity\Ec2;

/**
 * Provides the views data for the AWS Cloud Volume entity type.
 */
class VolumeViewsData extends AwsCloudViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $table_name = $this->storage->getEntityTypeId();
    $fields = $this->getFieldStorageDefinitions($table_name);

    $data[$table_name]['volume_bulk_form'] = [
      'title' => $this->t('Volume operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple volumes.'),
      'field' => [
        'id' => 'volume_bulk_form',
      ],
    ];

    // The following is a list of fields to turn from text search to
    // select list.  This list can be expanded through hook_views_data_alter().
    $selectable = [
      'size',
      'state',
      'volume_status',
      'volume_type',
      'iops',
      'availability_zone',
      'encrypted',
    ];

    $data[$table_name]['table']['base']['access query tag'] = "{$table_name}_views_access";
    $this->addDropdownSelector($data, $table_name, $fields, $selectable);

    return $data;
  }

}
