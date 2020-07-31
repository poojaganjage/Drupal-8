<?php

namespace Drupal\k8s\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a pod operations bulk form element.
 *
 * @ViewsField("pod_bulk_form")
 */
class K8sPodBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Pod selected.');
  }

}
