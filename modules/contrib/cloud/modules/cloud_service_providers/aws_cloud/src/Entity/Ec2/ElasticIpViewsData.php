<?php

namespace Drupal\aws_cloud\Entity\Ec2;

/**
 * Provides the views data for the CloudScripting entity type.
 */
class ElasticIpViewsData extends AwsCloudViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    $table_name = $this->storage->getEntityTypeId();
    $fields = $this->getFieldStorageDefinitions($table_name);

    $data[$table_name]['elastic_ip_bulk_form'] = [
      'title' => $this->t('Elastic IP operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Elastic IPs.'),
      'field' => [
        'id' => 'elastic_ip_bulk_form',
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
