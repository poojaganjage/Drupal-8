<?php

namespace Drupal\terraform\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the Workspace entity type.
 */
class TerraformWorkspaceViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['terraform_workspace']['workspace_bulk_form'] = [
      'title' => $this->t('Workspace operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple Workspaces.'),
      'field' => [
        'id' => 'workspace_bulk_form',
      ],
    ];

    return $data;
  }

}
