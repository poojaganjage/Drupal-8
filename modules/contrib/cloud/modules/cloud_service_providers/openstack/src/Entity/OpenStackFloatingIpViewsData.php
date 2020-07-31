<?php

namespace Drupal\openstack\Entity;

use Drupal\aws_cloud\Entity\Ec2\AwsCloudViewsData;

/**
 * Provides the views data for the CloudScripting entity type.
 */
class OpenStackFloatingIpViewsData extends AwsCloudViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    $table_name = $this->storage->getEntityTypeId();
    $fields = $this->getFieldStorageDefinitions($table_name);

    $data['openstack_floating_ip']['floating_ip_bulk_form'] = [
      'title' => $this->t('Floating IP operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Floating IPs.'),
      'field' => [
        'id' => 'floating_ip_bulk_form',
      ],
    ];

    // The following is a list of fields to turn from text search to
    // select list.  This list can be expanded through hook_views_data_alter().
    $selectable = [
      'domain',
      'scope',
      'network_interface_id',
      'allocation_id',
      'association_id',
    ];

    $this->addDropdownSelector($data, $table_name, $fields, $selectable);

    return $data;
  }

}
