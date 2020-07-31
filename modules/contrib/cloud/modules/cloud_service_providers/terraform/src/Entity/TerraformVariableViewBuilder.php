<?php

namespace Drupal\terraform\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the Variable view builders.
 */
class TerraformVariableViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'variable',
        'title' => $this->t('Variable'),
        'open' => TRUE,
        'fields' => [
          'name',
          'variable_id',
          'attribute_key',
          'attribute_value',
          'description',
          'category',
          'hcl',
          'sensitive',
          'created',
        ],
      ],
      [
        'name' => 'others',
        'title' => $this->t('Others'),
        'open' => FALSE,
        'fields' => [
          'cloud_context',
          'uid',
        ],
      ],
    ];
  }

}
