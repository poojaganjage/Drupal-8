<?php

namespace Drupal\terraform\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a variable operations bulk form element.
 *
 * @ViewsField("variable_bulk_form")
 */
class TerraformVariableBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Variable selected.');
  }

}
