<?php

namespace Drupal\aws_cloud\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'cidr_block' field type.
 *
 * @FieldType(
 *   id = "cidr_block",
 *   label = @Translation("AWS CIDR Block"),
 *   description = @Translation("AWS CIDR Block."),
 *   default_widget = "cidr_block_item",
 *   default_formatter = "cidr_block_formatter"
 * )
 */
class CidrBlock extends FieldItemBase {

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
    $properties['cidr'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('CIDR'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(TRUE);

    $properties['state'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Status'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(TRUE);

    $properties['status_message'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Status reason'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(TRUE);

    $properties['association_id'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Association ID'))
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
        'cidr' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
        'state' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
        'status_message' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
        'association_id' => [
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
    return empty($this->get('cidr')->getValue());
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
    // Unserialize the values.
    // @todo The storage controller should take care of this, see
    //   SqlContentEntityStorage::loadFieldItems, see
    //   https://www.drupal.org/node/2414835
    if (is_string($values['options'])) {
      $values['options'] = unserialize($values['options'], ['allowed_classes' => FALSE]);
    }
    parent::setValue($values, $notify);
  }

  /**
   * Get the cidr.
   */
  public function getCidr() {
    return $this->get('cidr')->getValue();
  }

  /**
   * Get the state.
   */
  public function getState() {
    return $this->get('state')->getValue();
  }

  /**
   * Get the status message.
   */
  public function getStatusMessage() {
    return $this->get('status_message')->getValue();
  }

  /**
   * Set the cidr.
   *
   * @param string $cidr
   *   The cidr.
   *
   * @return $this
   */
  public function setCidr($cidr) {
    return $this->set('cidr', $cidr);
  }

  /**
   * Set the state.
   *
   * @param string $state
   *   The state.
   *
   * @return $this
   */
  public function setState($state) {
    return $this->set('state', $state);
  }

  /**
   * Set the status message.
   *
   * @param string $status_message
   *   The status message.
   *
   * @return $this
   */
  public function setStatusMessage($status_message) {
    return $this->set('status_message', $status_message);
  }

}
