<?php

namespace Drupal\k8s\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'limit' field type.
 *
 * @FieldType(
 *   id = "limit",
 *   label = @Translation("Limit"),
 *   description = @Translation("K8s limit field."),
 *   default_widget = "limit_item",
 *   default_formatter = "limit_formatter"
 * )
 */
class Limit extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'max_length' => 255,
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $properties['limit_type'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Type'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(TRUE);

    $properties['resource'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Resource'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(TRUE);

    $properties['default'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Default'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'));

    $properties['default_request'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Default Request'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'));

    $properties['max'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Max'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'));

    $properties['min'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Min'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'));

    $properties['max_limit_request_ratio'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Max Limit Request Ratio'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'limit_type' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
        'resource' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
        'default' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
        'default_request' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
        'max' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
        'min' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
        'max_limit_request_ratio' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $elements = [];

    $elements['max_length'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum length'),
      '#default_value' => $this->getSetting('max_length'),
      '#required' => TRUE,
      '#description' => $this->t('The maximum length of the field in characters.'),
      '#min' => 1,
      '#disabled' => $has_data,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->get('limit_type')->getValue());
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    // Treat the values as property value of the main property, if no array is
    // given.
    if (isset($values) && !is_array($values)) {
      $values = [static::mainPropertyName() => $values];
    }
    if (isset($values)) {
      $values += [
        'options' => [],
      ];
    }

    if (is_string($values['options'])) {
      $values['options'] = unserialize($values['options'], ['allowed_classes' => FALSE]);
    }
    parent::setValue($values, $notify);
  }

  /**
   * Get the limit type.
   */
  public function getLimitType() {
    return $this->get('limit_type')->getValue();
  }

  /**
   * Set the limit type.
   *
   * @param string $limit_type
   *   The limit type.
   *
   * @return $this
   */
  public function setLimitType($limit_type) {
    return $this->set('limit_type', $limit_type);
  }

  /**
   * Get the resource.
   */
  public function getResoure() {
    return $this->get('resource')->getValue();
  }

  /**
   * Set the resource.
   *
   * @param string $resource
   *   The resource.
   *
   * @return $this
   */
  public function setResource($resource) {
    return $this->set('resource', $resource);
  }

  /**
   * Get the default.
   */
  public function getDefault() {
    return $this->get('default')->getValue();
  }

  /**
   * Set the default.
   *
   * @param string $default
   *   The default.
   *
   * @return $this
   */
  public function setDefault($default) {
    return $this->set('default', $default);
  }

  /**
   * Get the default request.
   */
  public function getDefaultRequest() {
    return $this->get('default_request')->getValue();
  }

  /**
   * Set the default request.
   *
   * @param string $default_request
   *   The default request.
   *
   * @return $this
   */
  public function setDefaultRequest($default_request) {
    return $this->set('default_request', $default_request);
  }

  /**
   * Get the max.
   */
  public function getMax() {
    return $this->get('max')->getValue();
  }

  /**
   * Set the max.
   *
   * @param string $max
   *   The max.
   *
   * @return $this
   */
  public function setMax($max) {
    return $this->set('max', $max);
  }

  /**
   * Get the min.
   */
  public function getMin() {
    return $this->get('min')->getValue();
  }

  /**
   * Set the min.
   *
   * @param string $min
   *   The min.
   *
   * @return $this
   */
  public function setMin($min) {
    return $this->set('min', $min);
  }

  /**
   * Get the max limit request ratio.
   */
  public function getMaxLimitRequestRatio() {
    return $this->get('max_limit_request_ratio')->getValue();
  }

  /**
   * Set the max limit request ratio.
   *
   * @param string $max_limit_request_ratio
   *   The max limit request ratio.
   *
   * @return $this
   */
  public function setMaxLimitRequestRatio($max_limit_request_ratio) {
    return $this->set('max_limit_request_ratio', $max_limit_request_ratio);
  }

}
