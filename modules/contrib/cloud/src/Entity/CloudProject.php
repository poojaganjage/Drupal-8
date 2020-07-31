<?php

namespace Drupal\cloud\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;

/**
 * Defines the cloud Project entity.
 *
 * @ingroup cloud_project
 *
 * @ContentEntityType(
 *   id = "cloud_project",
 *   id_plural = "cloud_projects",
 *   label = @Translation("Cloud Project"),
 *   label_collection = @Translation("Cloud Projects"),
 *   label_singular = @Translation("cloud project"),
 *   label_plural = @Translation("cloud projects"),
 *   bundle_label = @Translation("cloud project type"),
 *   handlers = {
 *     "storage"      = "Drupal\cloud\Entity\CloudProjectStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudProjectListBuilder",
 *     "views_data"   = "Drupal\cloud\Entity\CloudProjectViewsData",
 *     "translation"  = "Drupal\cloud\Entity\CloudProjectTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\cloud\Form\CloudProjectForm",
 *       "add"     = "Drupal\cloud\Form\CloudProjectForm",
 *       "edit"    = "Drupal\cloud\Form\CloudProjectForm",
 *       "delete"  = "Drupal\cloud\Form\CloudProjectDeleteForm",
 *       "launch"  = "Drupal\cloud\Form\CloudProjectLaunchConfirm",
 *       "copy"    = "Drupal\cloud\Form\CloudProjectForm",
 *       "delete-multiple-confirm" = "Drupal\cloud\Form\CloudProjectDeleteMultipleForm",
 *     },
 *     "access" = "Drupal\cloud\Controller\CloudProjectAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\cloud\Routing\CloudProjectHtmlRouteProvider",
 *     },
 *   },
 *   base_table          = "cloud_project",
 *   data_table          = "cloud_project_field_data",
 *   revision_table      = "cloud_project_revision",
 *   revision_data_table = "cloud_project_field_revision",
 *   translatable        = TRUE,
 *   admin_permission    = "administer cloud Projects",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle"   = "type",
 *     "label"    = "name",
 *     "uuid"     = "uuid",
 *     "uid"      = "uid",
 *     "langcode" = "langcode",
 *     "status"   = "status",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user"        = "revision_user",
 *     "revision_created"     = "revision_created",
 *     "revision_log_message" = "revision_log_message",
 *   },
 *   links = {
 *     "canonical"          = "/clouds/design/project/{cloud_context}/{cloud_project}",
 *     "add-form"           = "/clouds/design/project/{cloud_context}/{cloud_project_type}/add",
 *     "edit-form"          = "/clouds/design/project/{cloud_context}/{cloud_project}/edit",
 *     "delete-form"        = "/clouds/design/project/{cloud_context}/{cloud_project}/delete",
 *     "version-history"    = "/clouds/design/project/{cloud_context}/{cloud_project}/revisions",
 *     "revision"           = "/clouds/design/project/{cloud_context}/{cloud_project}/revisions/{cloud_project_revision}/view",
 *     "revision_revert"    = "/clouds/design/project/{cloud_context}/{cloud_project}/revisions/{cloud_project_revision}/revert",
 *     "revision_delete"    = "/clouds/design/project/{cloud_context}/{cloud_project}/revisions/{cloud_project_revision}/delete",
 *     "translation_revert" = "/clouds/design/project/{cloud_context}/{cloud_project}/revisions/{cloud_project_revision}/revert/{langcode}",
 *     "collection"         = "/clouds/design/project/{cloud_context}",
 *     "launch"             = "/clouds/design/project/{cloud_context}/{cloud_project}/launch",
 *     "copy"               = "/clouds/design/project/{cloud_context}/{cloud_project}/copy",
 *     "delete-multiple-form" = "/clouds/design/project/{cloud_context}/delete_multiple",
 *   },
 *   bundle_entity_type = "cloud_project_type",
 *   field_ui_base_route = "entity.cloud_project_type.edit_form"
 * )
 */
class CloudProject extends CloudContentEntityBase implements CloudProjectInterface {

  use EntityChangedTrait;

  public const TAG_CREATED_BY_UID = 'cloud_project_created_by_uid';

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'uid' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    // Add in cloud context.
    $uri_route_parameters['cloud_context'] = $this->getCloudContext();

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the
    // cloud_project owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

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
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCloudContext() {
    return $this->get('cloud_context')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCloudContext($cloud_context) {
    $this->set('cloud_context', $cloud_context);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Server Template.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 11,
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

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the cloud project.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    // @TODO: make this an entity reference to config entity?  For now, leave as string
    $fields['cloud_context'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Cloud Service Provider ID'))
      ->setRequired(TRUE)
      ->setDescription(t('A unique ID for the cloud service provider.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -3,
      ]);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the cloud project is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setReadOnly(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    parent::delete();
    parent::updateCache();
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    $return = parent::save();
    parent::updateCache();
    return $return;
  }

}
