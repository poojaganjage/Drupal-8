<?php

namespace Drupal\aws_cloud\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a snapshot operations bulk form element.
 *
 * @ViewsField("snapshot_bulk_form")
 */
class SnapshotBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No snapshot selected.');
  }

}
