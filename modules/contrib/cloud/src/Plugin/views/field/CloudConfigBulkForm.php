<?php

namespace Drupal\cloud\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines cloud service provider operations bulk form element.
 *
 * @ViewsField("cloud_config_bulk_form")
 */
class CloudConfigBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No cloud service provider selected.');
  }

}
