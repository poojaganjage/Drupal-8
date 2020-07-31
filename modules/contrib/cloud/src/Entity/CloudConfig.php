<?php

namespace Drupal\cloud\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;

/**
 * Defines the cloud service provider (CloudConfig) entity.
 *
 * @ingroup cloud
 *
 * @ContentEntityType(
 *   id = "cloud_config",
 *   id_plural = "cloud_configs",
 *   label = @Translation("Cloud Service Provider"),
 *   label_collection = @Translation("Cloud Service Providers"),
 *   label_singular = @Translation("cloud service provider"),
 *   label_plural = @Translation("cloud service providers"),
 *   bundle_label = @Translation("cloud service provide type"),
 *   handlers = {
 *     "storage"      = "Drupal\cloud\Entity\CloudConfigStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudConfigListBuilder",
 *     "views_data"   = "Drupal\cloud\Entity\CloudConfigViewsData",
 *     "translation"  = "Drupal\cloud\Entity\Drupal\cloud\Entity\CloudConfigStorage",
 *
 *     "form" = {
 *       "default"                 = "Drupal\cloud\Form\CloudConfigForm",
 *       "add"                     = "Drupal\cloud\Form\CloudConfigForm",
 *       "edit"                    = "Drupal\cloud\Form\CloudConfigForm",
 *       "delete"                  = "Drupal\cloud\Form\CloudConfigDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\cloud\Form\CloudConfigDeleteMultipleForm",
 *     },
 *     "access" = "Drupal\cloud\Controller\CloudConfigAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\cloud\Routing\CloudConfigHtmlRouteProvider",
 *     },
 *   },
 *   base_table          = "cloud_config",
 *   data_table          = "cloud_config_field_data",
 *   revision_table      = "cloud_config_revision",
 *   revision_data_table = "cloud_config_field_revision",
 *   translatable        = TRUE,
 *   admin_permission    = "administer cloud service providers",
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
 *     "canonical"            = "/admin/structure/cloud_config/{cloud_config}",
 *     "add-page"             = "/admin/structure/cloud_config/add",
 *     "add-form"             = "/admin/structure/cloud_config/add/{cloud_config_type}",
 *     "edit-form"            = "/admin/structure/cloud_config/{cloud_config}/edit",
 *     "delete-form"          = "/admin/structure/cloud_config/{cloud_config}/delete",
 *     "version-history"      = "/admin/structure/cloud_config/{cloud_config}/revisions",
 *     "revision"             = "/admin/structure/cloud_config/{cloud_config}/revisions/{cloud_config_revision}/view",
 *     "revision_revert"      = "/admin/structure/cloud_config/{cloud_config}/revisions/{cloud_config_revision}/revert",
 *     "revision_delete"      = "/admin/structure/cloud_config/{cloud_config}/revisions/{cloud_config_revision}/delete",
 *     "translation_revert"   = "/admin/structure/cloud_config/{cloud_config}/revisions/{cloud_config_revision}/revert/{langcode}",
 *     "collection"           = "/admin/structure/cloud_config",
 *     "delete-multiple-form" = "/admin/structure/cloud_config/delete_multiple",
 *   },
 *   bundle_entity_type  = "cloud_config_type",
 *   field_ui_base_route = "entity.cloud_config_type.edit_form"
 * )
 */
class CloudConfig extends CloudContentEntityBase implements CloudConfigInterface, CloudContextInterface {

  use EntityChangedTrait;

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

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) ?: [] as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly,
    // make the cloud_config owner the revision author.
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
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
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
  public function getIconFid() {
    $fid = $this->get('image')->target_id;
    if (!isset($fid)) {
      $fids = \Drupal::moduleHandler()->invokeAll(
        'default_cloud_config_icon', [
          $this,
        ]
      );

      if (count($fids) === 1 && !empty($fids[0])) {
        $fid = $fids[0];
      }
    }
    return $fid;
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    parent::delete();
    $this->deleteServerTemplate();

    // Note: In case of multiple entity deletion, if $this->updateCache() is
    // called, the first entity deletion clears the entire cache, so the
    // subsequent entities' cache will be gone, which the Drupal Core system
    // still requires to use during the multiple deletion process.  Therefore
    // it causes an error as follows:
    //
    // Uncaught PHP Exception Drupal\\Core\\Entity\\EntityStorageException:
    // "Cannot load cloud service provider (CloudConfig) plugin for
    // <cloud_context>"
    //
    // at core/lib/Drupal/Core/Entity/Sql/SqlContentEntityStorage.php line 7xx.
    //
    // Therefore $this->updateCache() has been moved to
    //
    // CloudConfigDeleteForm::submitForm and
    // CloudConfigProcessMultipleForm.
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    $return = parent::save();
    parent::updateCache();
    return $return;
  }

  /**
   * Delete cloud server template after cloud service provider deletion.
   */
  private function deleteServerTemplate() {
    $ids = \Drupal::entityQuery('cloud_server_template')
      ->condition('cloud_context', $this->getCloudContext())
      ->execute();
    if (count($ids)) {
      /* @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
      $entity_type_manager = \Drupal::entityTypeManager();
      $entities = $entity_type_manager->getStorage('cloud_server_template')
        ->loadMultiple($ids);
      $entity_type_manager->getStorage('cloud_server_template')->delete($entities);
    }
  }

  /**
   * Check if a specific cloud_context exists.
   *
   * Corresponds with the #machine_name widget type
   * when adding a new CloudConfig.
   *
   * @param string $cloud_context
   *   The cloud context.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   The cloud service provider (CloudConfig) entities.
   */
  public static function checkCloudContext($cloud_context) {
    $entity_type_manager = \Drupal::entityTypeManager();
    $storage = $entity_type_manager->getStorage('cloud_config');
    return $storage->loadByProperties(['cloud_context' => [$cloud_context]]);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the cloud service provider.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 99,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 99,
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
      ->setDescription(t('The name of the cloud service provider.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 128,
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

    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Icon'))
      ->setDescription(t('Icon representing the cloud service provider.'))
      ->setSettings([
        'file_directory' => 'IMAGE_FOLDER',
        'alt_field_required' => FALSE,
        'file_extensions' => 'png jpg jpeg',
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'cloud_image',
        'settings' => [
          'image_style' => 'icon_32x32',
        ],
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['cloud_context'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Cloud Service Provider ID'))
      ->setRequired(TRUE)
      ->setDescription(t('A unique ID for the cloud service provider.'))
      ->setRevisionable(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the cloud service provider (CloudConfig) is published.'))
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

}
