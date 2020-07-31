<?php

namespace Drupal\cloud_budget\Entity;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the cloud credit entity.
 *
 * @ingroup cloud_budget
 *
 * @ContentEntityType(
 *   id = "cloud_credit",
 *   id_plural = "cloud_credits",
 *   label = @Translation("Cloud Credit"),
 *   label_collection = @Translation("Cloud Credits"),
 *   label_singular = @Translation("Cloud Credit"),
 *   label_plural = @Translation("Cloud Credits"),
 *   handlers = {
 *     "view_builder" = "Drupal\cloud_budget\Entity\CloudCreditViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\cloud_budget\Entity\CloudCreditViewsData",
 *     "access"       = "Drupal\cloud_budget\Controller\CloudCreditAccessControlHandler",
 *     "form" = {
 *       "add"        = "Drupal\cloud_budget\Form\CloudCreditCreateForm",
 *       "edit"       = "Drupal\cloud_budget\Form\CloudCreditEditForm",
 *       "delete"     = "Drupal\cloud_budget\Form\CloudCreditDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\cloud_budget\Form\CloudBudgetDeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "cloud_credit",
 *   admin_permission = "administer cloud credit",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/budget/{cloud_context}/credit/{cloud_credit}",
 *     "collection"           = "/clouds/budget/{cloud_context}/credit",
 *     "add-form"             = "/clouds/budget/{cloud_context}/credit/add",
 *     "edit-form"            = "/clouds/budget/{cloud_context}/credit/{cloud_credit}/edit",
 *     "delete-form"          = "/clouds/budget/{cloud_context}/credit/{cloud_credit}/delete",
 *     "delete-multiple-form" = "/clouds/budget/{cloud_context}/credit/delete_multiple",
 *   },
 *   field_ui_base_route = "cloud_credit.settings"
 * )
 */
class CloudCredit extends CloudContentEntityBase implements CloudCreditInterface {

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
  public function getUser() {
    return $this->get('user')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setUser($user) {
    return $this->set('user', $user);
  }

  /**
   * {@inheritdoc}
   */
  public function getAmount() {
    return $this->get('amount')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAmount($amount) {
    return $this->set('amount', $amount);
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

    $fields['user'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setDescription(t('The User of the credit.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'author',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -5,
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

    $fields['amount'] = BaseFieldDefinition::create('float')
      ->setLabel(t('Amount'))
      ->setDescription(t('The amount of the credit.'))
      ->setRequired(TRUE)
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'number_decimal',
        'weight' => -5,
        'settings' => [
          'thousand_separator' => ',',
          'scale' => '2',
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
      ]);

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
