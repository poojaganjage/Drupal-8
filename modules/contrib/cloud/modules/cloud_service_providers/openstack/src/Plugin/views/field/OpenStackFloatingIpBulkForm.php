<?php

namespace Drupal\openstack\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a Floating IP operations bulk form element.
 *
 * @ViewsField("floating_ip_bulk_form")
 */
class OpenStackFloatingIpBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Floating IP selected.');
  }

}
