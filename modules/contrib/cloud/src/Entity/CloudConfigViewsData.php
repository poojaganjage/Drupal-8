<?php

namespace Drupal\cloud\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for cloud service provider (CloudConfig) entities.
 */
class CloudConfigViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $entity_type_id = $this->entityType->id();
    $data = parent::getViewsData();

    $data[$entity_type_id][$entity_type_id . '_bulk_form'] = [
      'title' => $this->t('Cloud service provider operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple cloud service providers.'),
      'field' => [
        'id' => 'cloud_config_bulk_form',
      ],
    ];

    // Additional information for Views integration, such as table joins, can be
    // put here.
    $data[$entity_type_id]['list_instances_' . $entity_type_id] = [
      'field' => [
        'title' => $this->t('Instances'),
        'help' => $this->t('Provide a listing link to instances'),
        'id' => 'cloud_list_instances',
      ],
    ];

    $data[$entity_type_id]['pricing_internal_' . $entity_type_id] = [
      'field' => [
        'title' => $this->t('Pricing (internal)'),
        'help' => $this->t('Provide a link to internal pricing.'),
        'id' => 'cloud_pricing_internal',
      ],
    ];

    $data[$entity_type_id]['pricing_external_' . $entity_type_id] = [
      'field' => [
        'title' => $this->t('Pricing (external)'),
        'help' => $this->t('Provide a link to external pricing.'),
        'id' => 'cloud_pricing_external',
      ],
    ];

    $data[$entity_type_id]['list_server_templates_' . $entity_type_id] = [
      'field' => [
        'title' => $this->t('Launch templates'),
        'help' => $this->t('Provide a listing link to launch template'),
        'id' => 'cloud_list_templates',
      ],
    ];

    return $data;
  }

}
