<?php

namespace Drupal\k8s\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a network policy operations bulk form element.
 *
 * @ViewsField("network_policy_bulk_form")
 */
class K8sNetworkPolicyBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Network Policy selected.');
  }

}
