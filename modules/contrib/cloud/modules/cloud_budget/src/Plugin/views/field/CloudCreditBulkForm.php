<?php

namespace Drupal\cloud_budget\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a cloud credit operations bulk form element.
 *
 * @ViewsField("cloud_credit_bulk_form")
 */
class CloudCreditBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Credit selected.');
  }

}
