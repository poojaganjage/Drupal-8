<?php

namespace Drupal\openstack\Entity;

use Drupal\aws_cloud\Entity\Ec2\AwsCloudViewsData;

/**
 * Provides the views data for the CloudScripting entity type.
 */
class OpenStackKeyPairViewsData extends AwsCloudViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['openstack_key_pair']['key_pair_bulk_form'] = [
      'title' => $this->t('Key Pair operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Key Pairs.'),
      'field' => [
        'id' => 'key_pair_bulk_form',
      ],
    ];

    $table_name = $this->storage->getEntityTypeId();
    $fields = $this->getFieldStorageDefinitions($table_name);

    // The following is a list of fields to turn from text search to
    // select list.  This list can be expanded through hook_views_data_alter().
    $selectable = [
      'key_pair_name',
      'key_fingerprint',
    ];

    $data['openstack_key_pair']['table']['base']['access query tag'] = 'openstack_key_pair_views_access';
    $this->addDropdownSelector($data, $table_name, $fields, $selectable);

    return $data;
  }

}
