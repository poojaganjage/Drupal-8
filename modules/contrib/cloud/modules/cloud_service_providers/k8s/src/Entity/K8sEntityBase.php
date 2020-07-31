<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the base of K8s entity.
 */
class K8sEntityBase extends CloudContentEntityBase implements K8sEntityInterface {

  public const ANNOTATION_CREATED_BY_UID = 'k8s_created_by_uid';

  public const ANNOTATION_LAUNCHED_APPLICATION_ID = 'k8s_launched_application_id';

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    return $this->set('name', $name);
  }

  /**
   * {@inheritdoc}
   */
  public function getLabels() {
    return $this->get('labels')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function setLabels(array $labels) {
    return $this->set('labels', $labels);
  }

  /**
   * {@inheritdoc}
   */
  public function getAnnotations() {
    return array_column($this->get('annotations')->getValue(), 'item_value', 'item_key');
  }

  /**
   * {@inheritdoc}
   */
  public function setAnnotations(array $annotations) {
    return $this->set('annotations', $annotations);
  }

  /**
   * {@inheritdoc}
   */
  public function getDetail() {
    return $this->get('detail')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDetail($detail) {
    return $this->set('detail', $detail);
  }

  /**
   * {@inheritdoc}
   */
  public function setCreated($created = 0) {
    return $this->set('created', $created);
  }

  /**
   * {@inheritdoc}
   */
  public function getRefreshed() {
    return $this->get('refreshed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRefreshed($time) {
    return $this->set('refreshed', $time);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the entity.'))
      ->setReadOnly(TRUE);

    $fields['cloud_context'] = BaseFieldDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel(t('Cloud Service Provider ID'))
      ->setDescription(t('A unique ID for the cloud service provider.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The entity name.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['labels'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Labels'))
      ->setDescription(t('Map of string keys and values that can be used to organize and categorize (scope and select) objects.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    $fields['annotations'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Annotations'))
      ->setDescription(t('Annotations is an unstructured key value map stored with a resource that may be set by external tools to store and retrieve arbitrary metadata.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 4096)
      ->setSetting('long', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    $fields['detail'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Detail'))
      ->setDescription(t('Entity detail.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'pre_string_formatter',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'settings' => [
          'rows' => 20,
        ],
      ])
      ->addConstraint('yaml_array_data');

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['refreshed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Refreshed'))
      ->setDescription(t('The time that the entity was last refreshed.'));

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of the entity author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'match_limit' => 10,
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
