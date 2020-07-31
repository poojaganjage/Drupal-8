<?php

namespace Drupal\aws_cloud\Entity\Ec2;

/**
 * Provides the views data for the CloudScripting entity type.
 */
class SnapshotViewsData extends AwsCloudViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['aws_cloud_snapshot']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('AWS Cloud Snapshot'),
      'help'  => $this->t('The AWC Cloud Snapshot entity ID.'),
    ];

    $table_name = $this->storage->getEntityTypeId();
    $fields = $this->getFieldStorageDefinitions($table_name);

    $data[$table_name]['snapshot_bulk_form'] = [
      'title' => $this->t('Snapshot operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple snapshots.'),
      'field' => [
        'id' => 'snapshot_bulk_form',
      ],
    ];

    // The following is a list of fields to turn from text search to
    // select list.  This list can be expanded through hook_views_data_alter().
    $selectable = [
      'size',
      'volume_id',
      'account_id',
      'encrypted',
      'capacity',
    ];

    $data[$table_name]['table']['base']['access query tag'] = "{$table_name}_views_access";
    $this->addDropdownSelector($data, $table_name, $fields, $selectable);

    return $data;
  }

}
