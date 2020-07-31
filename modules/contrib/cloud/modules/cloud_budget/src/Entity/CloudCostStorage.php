<?php

namespace Drupal\cloud_budget\Entity;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Cloud Cost Storage entity.
 *
 * @ingroup cloud_budget
 *
 * @ContentEntityType(
 *   id = "cloud_cost_storage",
 *   id_plural = "cloud_cost_storages",
 *   label = @Translation("Cloud Cost Storage"),
 *   label_collection = @Translation("Cloud Cost Storages"),
 *   label_singular = @Translation("Cloud Cost Storage"),
 *   label_plural = @Translation("Cloud Cost Storages"),
 *   handlers = {
 *     "view_builder" = "Drupal\cloud_budget\Entity\CloudCostStorageViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\cloud_budget\Entity\CloudCostStorageViewsData",
 *     "access"       = "Drupal\cloud_budget\Controller\CloudCostStorageAccessControlHandler",
 *   },
 *   base_table = "cloud_cost_storage",
 *   entity_keys = {
 *     "id"    = "id",
 *     "uuid"  = "uuid",
 *   },
 *   field_ui_base_route = "cloud_cost_storage.settings"
 * )
 */
class CloudCostStorage extends CloudContentEntityBase implements CloudCostStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function getPayer() {
    return $this->get('payer')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPayer($payer) {
    return $this->set('payer', $payer);
  }

  /**
   * {@inheritdoc}
   */
  public function getCost() {
    return $this->get('cost')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCost($cost) {
    return $this->set('cost', $cost);
  }

  /**
   * {@inheritdoc}
   */
  public function getResources() {
    return $this->get('resources')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setResources($resources) {
    return $this->set('resources', $resources);
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
  public function getChanged() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setChanged($created = 0) {
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
  public function label() {
    return $this->getUser()->getDisplayName();
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

    $fields['payer'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Payer'))
      ->setDescription(t('The Payer of the cost.'));

    $fields['cost'] = BaseFieldDefinition::create('float')
      ->setLabel(t('Cost'))
      ->setDescription(t('The amount of the usage cost.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'list_float',
        'weight' => -5,
        'settings' => [
          'thousand_separator' => ',',
          'scale' => '2',
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
      ]);

    $fields['resources'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Resources'))
      ->setDescription(t('Detail resources.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'pre_string_formatter',
        'weight' => -5,
      ])
      ->addConstraint('yaml_array_data');

    $fields['refreshed'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Refreshed'))
      ->setDescription(t('The time that the entity was last refreshed.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'timestamp_ago',
        'weight' => -5,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

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
