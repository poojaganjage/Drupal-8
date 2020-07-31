<?php

namespace Drupal\aws_cloud\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a key pair operations bulk form element.
 *
 * @ViewsField("key_pair_bulk_form")
 */
class KeyPairBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No key pair selected.');
  }

}
