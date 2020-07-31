<?php

namespace Drupal\k8s\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a namespace operations bulk form element.
 *
 * @ViewsField("namespace_bulk_form")
 */
class K8sNamespaceBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Namespace selected.');
  }

}
