<?php

namespace Drupal\aws_cloud\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a network interface operations bulk form element.
 *
 * @ViewsField("network_interface_bulk_form")
 */
class NetworkInterfaceBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No network interface selected.');
  }

}
