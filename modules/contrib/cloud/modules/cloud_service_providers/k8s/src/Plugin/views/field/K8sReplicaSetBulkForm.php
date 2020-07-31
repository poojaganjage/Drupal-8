<?php

namespace Drupal\k8s\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a replica set operations bulk form element.
 *
 * @ViewsField("replica_set_bulk_form")
 */
class K8sReplicaSetBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No replica set selected.');
  }

}
