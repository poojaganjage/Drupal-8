<?php

namespace Drupal\k8s\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a deployment operations bulk form element.
 *
 * @ViewsField("deployment_bulk_form")
 */
class K8sDeploymentBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No Deployment selected.');
  }

}
