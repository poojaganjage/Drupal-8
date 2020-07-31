<?php

namespace Drupal\k8s\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a limit range operations bulk form element.
 *
 * @ViewsField("limit_range_bulk_form")
 */
class K8sLimitRangeBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Limit Range selected.');
  }

}
