<?php

namespace Drupal\aws_cloud\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a subnet operations bulk form element.
 *
 * @ViewsField("subnet_bulk_form")
 */
class SubnetBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Subnet selected.');
  }

}
