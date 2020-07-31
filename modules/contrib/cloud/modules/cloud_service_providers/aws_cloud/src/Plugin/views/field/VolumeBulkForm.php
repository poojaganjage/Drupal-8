<?php

namespace Drupal\aws_cloud\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a volume operations bulk form element.
 *
 * @ViewsField("volume_bulk_form")
 */
class VolumeBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No volume selected.');
  }

}
