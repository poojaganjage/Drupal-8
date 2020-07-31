<?php

namespace Drupal\terraform\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a workspace operations bulk form element.
 *
 * @ViewsField("workspace_bulk_form")
 */
class TerraformWorkspaceBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Workspace selected.');
  }

}
