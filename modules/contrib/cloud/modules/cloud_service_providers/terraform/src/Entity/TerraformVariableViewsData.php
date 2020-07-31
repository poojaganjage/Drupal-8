<?php

namespace Drupal\terraform\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the Variable entity type.
 */
class TerraformVariableViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['terraform_variable']['variable_bulk_form'] = [
      'title' => $this->t('Variable operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Variables.'),
      'field' => [
        'id' => 'variable_bulk_form',
      ],
    ];

    return $data;
  }

}
