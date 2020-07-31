<?php

namespace Drupal\k8s\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a cron job operations bulk form element.
 *
 * @ViewsField("cron_job_bulk_form")
 */
class K8sCronJobBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Cron Job selected.');
  }

}
