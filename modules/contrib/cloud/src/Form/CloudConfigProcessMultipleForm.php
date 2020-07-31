<?php

namespace Drupal\cloud\Form;

/**
 * Provide a confirmation form of bulk operation for cloud service providers.
 */
abstract class CloudConfigProcessMultipleForm extends CloudProcessMultipleForm {

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId(): string {

    return 'cloud_config_process_multiple_confirm_form';
  }

}
