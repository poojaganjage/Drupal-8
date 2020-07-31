<?php

namespace Drupal\k8s\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a network policy operations bulk form element.
 *
 * @ViewsField("persistent_volume_bulk_form")
 */
class K8sPersistentVolumeBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Persistent Volume selected.');
  }

}
