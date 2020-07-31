<?php

namespace Drupal\k8s\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a priority class operations bulk form element.
 *
 * @ViewsField("priority_class_bulk_form")
 */
class K8sPriorityClassBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Priority Class selected.');
  }

}
