<?php

namespace Drupal\k8s\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a job operations bulk form element.
 *
 * @ViewsField("job_bulk_form")
 */
class K8sJobBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Job selected.');
  }

}
