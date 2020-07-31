<?php

namespace Drupal\cloud\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'key_value' field type.
 *
 * @FieldType(
 *   id = "key_value",
 *   label = @Translation("Key Value"),
 *   description = @Translation("Key value field."),
 *   default_widget = "key_value_item",
 *   default_formatter = "key_value_formatter"
 * )
 */
class KeyValue extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'max_length' => 255,
      'long' => FALSE,
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['item_key'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Key'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(TRUE);

    $properties['item_value'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Value'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'item_key' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
      ],
    ];
    if ($field_definition->getSetting('long')) {
      $schema['columns']['item_value'] = [
        'type' => 'text',
        'size' => 'big',
      ];
    }
    else {
      $schema['columns']['item_value'] = [
        'type' => 'varchar',
        'length' => (int) $field_definition->getSetting('max_length'),
      ];
    }

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

    $elements['long'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Long'),
      '#default_value' => $this->getSetting('long'),
      '#required' => TRUE,
      '#description' => $this->t('The content is long or short.'),
      '#disabled' => $has_data,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->get('item_key')->getValue());
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
   * Get the key.
   */
  public function getItemKey() {
    return $this->get('item_key')->getValue();
  }

  /**
   * Get the value.
   */
  public function getItemValue() {
    return $this->get('item_value')->getValue();
  }

  /**
   * Set the key.
   *
   * @param string $key
   *   The key.
   *
   * @return $this
   */
  public function setItemKey($key) {
    return $this->set('item_key', $key);
  }

  /**
   * Set the value.
   *
   * @param string $value
   *   The value.
   *
   * @return $this
   */
  public function setItemValue($value) {
    return $this->set('item_value', $value);
  }

}
