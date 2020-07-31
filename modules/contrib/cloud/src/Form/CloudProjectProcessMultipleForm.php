<?php

namespace Drupal\cloud\Form;

/**
 * Provide a confirmation form of bulk operation for cloud projects.
 */
abstract class CloudProjectProcessMultipleForm extends CloudProcessMultipleForm {

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId(): string {

    return 'cloud_project_process_multiple_confirm_form';
  }

}
