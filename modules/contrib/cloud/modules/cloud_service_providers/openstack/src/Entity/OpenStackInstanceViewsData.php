<?php

namespace Drupal\openstack\Entity;

use Drupal\aws_cloud\Entity\Ec2\AwsCloudViewsData;

/**
 * Provides Views data for OpenStack Instance entities.
 */
class OpenStackInstanceViewsData extends AwsCloudViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $table_name = $this->storage->getEntityTypeId();
    $fields = $this->getFieldStorageDefinitions($table_name);

    $data[$table_name]['instance_bulk_form'] = [
      'title' => $this->t('Instance operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple instances.'),
      'field' => [
        'id' => 'instance_bulk_form',
      ],
    ];

    // The following is a list of fields to turn from text search to
    // select list.  This list can be expanded through hook_views_data_alter().
    $selectable = [
      'instance_state',
      'instance_type',
      'availability_zone',
      'key_pair_name',
      'image_id',
    ];

    // Add an access query tag.
    $data[$table_name]['table']['base']['access query tag'] = 'openstack_instance_views_access';
    $this->addDropdownSelector($data, $table_name, $fields, $selectable);

    return $data;
  }

}
