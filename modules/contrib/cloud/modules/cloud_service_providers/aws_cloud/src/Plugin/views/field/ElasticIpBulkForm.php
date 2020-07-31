<?php

namespace Drupal\aws_cloud\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an Elastic IP operations bulk form element.
 *
 * @ViewsField("elastic_ip_bulk_form")
 */
class ElasticIpBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Elastic IP selected.');
  }

}
