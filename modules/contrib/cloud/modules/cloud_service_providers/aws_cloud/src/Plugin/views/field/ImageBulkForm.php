<?php

namespace Drupal\aws_cloud\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a image operations bulk form element.
 *
 * @ViewsField("image_bulk_form")
 */
class ImageBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No image selected.');
  }

}
