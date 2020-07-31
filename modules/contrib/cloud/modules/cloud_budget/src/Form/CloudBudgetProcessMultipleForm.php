<?php

namespace Drupal\cloud_budget\Form;

use Drupal\cloud\Form\CloudProcessMultipleForm;

/**
 * Provide a confirmation form of bulk operation for cloud budgets.
 */
abstract class CloudBudgetProcessMultipleForm extends CloudProcessMultipleForm {

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId(): string {

    return 'cloud_budget_process_multiple_confirm_form';
  }

}
