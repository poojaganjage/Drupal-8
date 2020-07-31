<?php

namespace Drupal\openstack\Entity;

use Drupal\aws_cloud\Entity\Ec2\AwsCloudViewsData;

/**
 * Provides the views data for the CloudScripting entity type.
 */
class OpenStackSnapshotViewsData extends AwsCloudViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['openstack_snapshot']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('OpenStack Snapshot'),
      'help'  => $this->t('The AWC Cloud Snapshot entity ID.'),
    ];

    $data['openstack_snapshot']['snapshot_bulk_form'] = [
      'title' => $this->t('Snapshot operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple snapshots.'),
      'field' => [
        'id' => 'snapshot_bulk_form',
      ],
    ];

    $table_name = $this->storage->getEntityTypeId();
    $fields = $this->getFieldStorageDefinitions($table_name);

    // The following is a list of fields to turn from text search to
    // select list.  This list can be expanded through hook_views_data_alter().
    $selectable = [
      'size',
      'volume_id',
      'account_id',
      'encrypted',
      'capacity',
    ];

    $data['openstack_snapshot']['table']['base']['access query tag'] = 'openstack_snapshot_views_access';
    $this->addDropdownSelector($data, $table_name, $fields, $selectable);

    return $data;
  }

}
