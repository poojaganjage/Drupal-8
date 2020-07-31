<?php

namespace Drupal\aws_cloud\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a security group operations bulk form element.
 *
 * @ViewsField("security_group_bulk_form")
 */
class SecurityGroupBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Security Group selected.');
  }

}
