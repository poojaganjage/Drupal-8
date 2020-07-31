<?php

namespace Drupal\aws_cloud\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a VPC Peering Connection operations bulk form element.
 *
 * @ViewsField("vpc_peering_connection_bulk_form")
 */
class VpcPeeringConnectionBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No VPC Peering Connection selected.');
  }

}
