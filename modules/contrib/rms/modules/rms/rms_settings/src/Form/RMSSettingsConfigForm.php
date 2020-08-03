<?php

namespace Drupal\rms_settings\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Defines a form that configures RMS admin settings.
 */
class RMSSettingsConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rms_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'rms_admin_settings.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('rms_admin_settings.settings');

    $form['job_id_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Job Id Prefix'),
      '#default_value' => $config->get('job_id_prefix'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('rms_admin_settings.settings')
      ->set('job_id_prefix', $form_state->getValue('job_id_prefix'))
      ->save();
  }

}
