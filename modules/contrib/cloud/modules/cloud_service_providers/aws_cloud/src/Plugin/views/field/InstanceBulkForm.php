<?php

namespace Drupal\aws_cloud\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a instance operations bulk form element.
 *
 * @ViewsField("instance_bulk_form")
 */
class InstanceBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No instance selected.');
  }

}
