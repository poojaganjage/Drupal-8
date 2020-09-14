<?php

namespace Drupal\smart_content\Plugin\smart_content\Condition\Type;

use Drupal\Core\Form\FormStateInterface;
use Drupal\smart_content\Condition\Type\ConditionTypeBase;

/**
 * Provides a 'key_value' ConditionType.
 *
 * @SmartConditionType(
 *  id = "key_value",
 *  label = @Translation("KeyValue"),
 * )
 */
class KeyValue extends ConditionTypeBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'][] = 'condition-key-value';
    $form['key'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => isset($this->configuration['key']) ? $this->configuration['key'] : $this->defaultFieldConfiguration()['key'],
      '#attributes' => ['class' => ['condition-key']],
      '#size' => 20,
      '#required' => TRUE,
    ];
    $form['op'] = [
      '#type' => 'select',
      '#options' => $this->getOperators(),
      '#default_value' => isset($this->configuration['op']) ? $this->configuration['op'] : $this->defaultFieldConfiguration()['op'],
      '#attributes' => ['class' => ['condition-op']],
    ];
    $form['value'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => isset($this->configuration['value']) ? $this->configuration['value'] : $this->defaultFieldConfiguration()['value'],
      '#attributes' => ['class' => ['condition-value']],
      // @todo: make configurable
      '#size' => 20,
    ];

    $form['#process'][] = [$this, 'buildWidget'];
    return $form;
  }

  /**
   * Process callback for accessing parents.
   */
  public function buildWidget(array &$element, FormStateInterface $form_state, array &$complete_form) {
    if (!empty($element['#parents'])) {
      $parents = $element['#parents'];
      $first_item = array_shift($parents);

      array_walk($parents, function (&$value, $i) {
        $value = '[' . $value . ']';
      });

      $parent_string = $first_item . implode('', $parents) . '[op]';

      $element['value']['#states'] = [
        'invisible' => [
          'select[name="' . $parent_string . '"]' => [
            ['value' => 'empty'],
            ['value' => 'is_set'],
          ],
        ],
      ];
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultFieldConfiguration() {
    return [
      'key' => '',
      'op' => 'equals',
      'value' => '',
    ];
  }

  /**
   * Returns a list of operators.
   */
  public function getOperators() {
    return [
      'equals' => $this->t('Equals'),
      'starts_with' => $this->t('Starts with'),
      'empty' => $this->t('Is empty'),
      'is_set' => $this->t('Is set'),
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
