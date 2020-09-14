<?php

namespace Drupal\smart_content\Plugin\smart_content\Condition\Type;

use Drupal\Core\Form\FormStateInterface;
use Drupal\smart_content\Condition\Type\ConditionTypeBase;

/**
 * Provides a 'number' ConditionType.
 *
 * @SmartConditionType(
 *  id = "number",
 *  label = @Translation("Number"),
 * )
 */
class Number extends ConditionTypeBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $condition_definition = $this->conditionInstance->getPluginDefinition();
    $form['op'] = [
      '#type' => 'select',
      '#options' => $this->getOperators(),
      '#default_value' => isset($this->configuration['op']) ? $this->configuration['op'] : $this->defaultFieldConfiguration()['op'],
    ];
    $form['value'] = [
      '#type' => 'number',
      '#required' => TRUE,
      '#default_value' => isset($this->configuration['value']) ? $this->configuration['value'] : $this->defaultFieldConfiguration()['value'],
    ];
    if (isset($condition_definition['format_options']['prefix'])) {
      $form['value']['#prefix'] = $condition_definition['format_options']['prefix'];
    }
    if (isset($condition_definition['format_options']['suffix'])) {
      $form['value']['#suffix'] = $condition_definition['format_options']['suffix'];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultFieldConfiguration() {
    return [
      'op' => 'equals',
      'value' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getOperators() {
    return [
      'equals' => '=',
      'gt' => '>',
      'lt' => '<',
      'gte' => '>=',
      'lte' => '<=',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    return ['smart_content/condition_type.standard'];
  }

  /**
   * {@inheritdoc}
   */
  public function getAttachedSettings() {
    return $this->getConfiguration() + $this->defaultFieldConfiguration();
  }

}
