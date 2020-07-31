<?php

namespace Drupal\k8s\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a resource quota operations bulk form element.
 *
 * @ViewsField("resource_quota_bulk_form")
 */
class K8sResourceQuotaBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Resource Quota selected.');
  }

}
