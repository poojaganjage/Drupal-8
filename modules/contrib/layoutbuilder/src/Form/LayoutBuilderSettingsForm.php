<?php
/**
  * @file
  * Contains \Drupal\layoutbuilder\Form\LayoutBuilderSettingsForm
  */
namespace Drupal\layoutbuilder\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symphony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
  * Defines a form to configure LayoutBuilder module settings
  */

class LayoutBuilderSettingsForm extends ConfigFormBase {
  /**
    * {@inheritdoc}
    */
  public function getFormID() {
  	return 'layoutbuilder_admin_settings';
  }
  /**
    * {@inheritdoc}
    */
  protected function getEditableConfigNames() {
  	return [
  		'layoutbuilder.settings'
  	];
  }
  /**
    * {@inheritdoc}
    */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
  	$types = node_type_get_names();
    $config = $this->config('layoutbuilder.settings');
    $form['entities'] = [
      '#title' => $this->t('Entities'),
      '#description' => $this->t('Select the entities for which this field will be made available.'),
      '#type' => 'checkboxes',
      '#required' => TRUE,
      '#options' => $types,
      '#default_value' => $config->get('layoutbuilder.allowed_types.entity'),
    ];
  	return parent::buildForm($form, $form_state);
  }
  /**
    * {@inheritdoc}
    */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  	$allowed_types = array_filter($form_state->getValue('entities'));
  	sort($allowed_types);
  	$this->config('layoutbuilder.settings')
  	  ->set('layoutbuilder.allowed_types.entity', $allowed_types)
  	  ->save();
  	  parent::submitForm($form, $form_state);
  }
}