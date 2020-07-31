<?php

namespace Drupal\k8s\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a service operations bulk form element.
 *
 * @ViewsField("service_bulk_form")
 */
class K8sServiceBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Service selected.');
  }

}
