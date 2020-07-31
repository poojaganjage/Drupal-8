<?php

namespace Drupal\k8s\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'rule' field type.
 *
 * @FieldType(
 *   id = "rule",
 *   label = @Translation("rule"),
 *   description = @Translation("K8s rule field."),
 *   default_widget = "rule_item",
 *   default_formatter = "rule_formatter"
 * )
 */
class Rule extends FieldItemBase {

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

    $properties['verbs'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Verbs'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(TRUE);

    $properties['resources'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Resources'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(TRUE);

    $properties['api_groups'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('API Groups'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'));

    $properties['resource_names'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Resource Names'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'verbs' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
        'resources' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
        'api_groups' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
        'resource_names' => [
          'type' => 'text',
          'size' => 'big',
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
    return empty($this->get('resources')->getValue());
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
   * Get the verbs.
   */
  public function getVerbs() {
    return $this->get('verbs')->getValue();
  }

  /**
   * Set the verbs.
   *
   * @param string $verbs
   *   The verbs.
   *
   * @return $this
   */
  public function setVerbs($verbs) {
    return $this->set('verbs', $verbs);
  }

  /**
   * Get the resources.
   */
  public function getResoures() {
    return $this->get('resources')->getValue();
  }

  /**
   * Set the resources.
   *
   * @param string $resources
   *   The resources.
   *
   * @return $this
   */
  public function setResources($resources) {
    return $this->set('resources', $resources);
  }

  /**
   * Get the API groups.
   */
  public function getApiGroups() {
    return $this->get('api_groups')->getValue();
  }

  /**
   * Set the API groups.
   *
   * @param string $api_groups
   *   The API groups.
   *
   * @return $this
   */
  public function setApiGroups($api_groups) {
    return $this->set('api_groups', $api_groups);
  }

  /**
   * Get the resource names.
   */
  public function getResourceNames() {
    return $this->get('resource_names')->getValue();
  }

  /**
   * Set the resource names.
   *
   * @param string $resource_names
   *   The resource names.
   *
   * @return $this
   */
  public function setResourceNames($resource_names) {
    return $this->set('resource_names', $resource_names);
  }

}
