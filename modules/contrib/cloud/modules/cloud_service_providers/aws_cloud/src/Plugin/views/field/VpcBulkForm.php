<?php

namespace Drupal\aws_cloud\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a VPC operations bulk form element.
 *
 * @ViewsField("vpc_bulk_form")
 */
class VpcBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No VPC selected.');
  }

}
