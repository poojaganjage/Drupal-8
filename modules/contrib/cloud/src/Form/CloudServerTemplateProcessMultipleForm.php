<?php

namespace Drupal\cloud\Form;

/**
 * Provide a confirmation form of bulk operation for cloud server templates.
 */
abstract class CloudServerTemplateProcessMultipleForm extends CloudProcessMultipleForm {

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId(): string {

    return 'cloud_server_template_process_multiple_confirm_form';
  }

}
