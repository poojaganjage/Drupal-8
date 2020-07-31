<?php

namespace Drupal\aws_cloud\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'block_device_mappings' field type.
 *
 * @FieldType(
 *   id = "block_device_mappings",
 *   label = @Translation("AWS Block Device Mappings"),
 *   description = @Translation("AWS Block Device Mappings."),
 *   default_widget = "block_device_mappings_item",
 *   default_formatter = "block_device_mappings_formatter"
 * )
 */
class BlockDeviceMappings extends FieldItemBase {

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
    $properties['device_name'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Device Name'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(FALSE);

    $properties['virtual_name'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Virtual Name'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(FALSE);

    $properties['delete_on_termination'] = DataDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Delete On Termination'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(FALSE);

    $properties['snapshot_id'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Snapshot ID'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(FALSE);

    $properties['volume_size'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Volume Size'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(FALSE);

    $properties['volume_type'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Volume Type'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(FALSE);

    $properties['encrypted'] = DataDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Encrypted'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'device_name' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
        'virtual_name' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
        'delete_on_termination' => [
          'type' => 'int',
          'size' => 'tiny',
        ],
        'snapshot_id' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
        'volume_size' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
        'volume_type' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
        ],
        'encrypted' => [
          'type' => 'int',
          'size' => 'tiny',
        ],
      ],
    ];
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
    return empty($this->get('device_name')->getValue());
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
   * Get the device name.
   */
  public function getDeviceName() {
    return $this->get('device_name')->getValue();
  }

  /**
   * Get the virtual name.
   */
  public function getVirtualName() {
    return $this->get('virtual_name')->getValue();
  }

  /**
   * Get delete on termination.
   */
  public function isDeleteOnTermination() {
    return $this->get('delete_on_termination')->getValue();
  }

  /**
   * Get snapshot id.
   */
  public function getSnapshotId() {
    return $this->get('snapshot_id')->getValue();
  }

  /**
   * Get volume size.
   */
  public function getVolumeSize() {
    return $this->get('volume_size')->getValue();
  }

  /**
   * Get volume type.
   */
  public function getVolumeType() {
    return $this->get('volume_type')->getValue();
  }

  /**
   * Get encrypted flag.
   */
  public function isEncrypted() {
    return $this->get('encrypted')->getValue();
  }

  /**
   * Set the device name.
   *
   * @param string $device_name
   *   The device name to set.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *   Throws missing data exception.
   */
  public function setDeviceName($device_name) {
    $this->set('device_name', $device_name);
  }

  /**
   * Set the virtual name.
   *
   * @param string $virtual_name
   *   The virtual name to set.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *   Throws missing data exception.
   */
  public function setVirtualName($virtual_name) {
    $this->set('virtual_name', $virtual_name);
  }

  /**
   * Set the delete on termination.
   *
   * @param string $delete_on_termination
   *   The delete_on_termination string.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *   Throws missing data exception.
   */
  public function setDeleteOnTermination($delete_on_termination) {
    $this->set('delete_on_termination', $delete_on_termination);
  }

  /**
   * Set the snapshot_id.
   *
   * @param string $snapshot_id
   *   The snapshot_id string.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *   Throws missing data exception.
   */
  public function setSnapshotId($snapshot_id) {
    $this->set('snapshot_id', $snapshot_id);
  }

  /**
   * Set the volume_size.
   *
   * @param string $volume_size
   *   The volume_size.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *   Throws missing data exception.
   */
  public function setVolumeSize($volume_size) {
    $this->set('volume_size', $volume_size);
  }

  /**
   * Set the volume_type.
   *
   * @param string $volume_type
   *   The volume_type string.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *   Throws missing data exception.
   */
  public function setVolumeType($volume_type) {
    $this->set('volume_type', $volume_type);
  }

  /**
   * Set the encrypted flag.
   *
   * @param string $encrypted
   *   The encrypted string.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *   Throws missing data exception.
   */
  public function setEncrypted($encrypted) {
    $this->set('encrypted', $encrypted);
  }

}
