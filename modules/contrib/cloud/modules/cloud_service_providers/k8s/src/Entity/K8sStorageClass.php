<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the Storage Class entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_storage_class",
 *   id_plural = "k8s_storage_classes",
 *   label = @Translation("Storage Class"),
 *   label_collection = @Translation("Storage Classes"),
 *   namespaceable = FALSE,
 *   label_singular = @Translation("Storage Class"),
 *   label_plural = @Translation("Storage Classes"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sStorageClassViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sStorageClassViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sStorageClassAccessControlHandler",
 *     "form" = {
 *       "add"        = "Drupal\k8s\Form\K8sCreateForm",
 *       "edit"       = "Drupal\k8s\Form\K8sEditForm",
 *       "delete"     = "Drupal\k8s\Form\K8sDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\k8s\Form\K8sDeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "k8s_storage_class",
 *   admin_permission = "administer k8s storage class",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/storage_class/{k8s_storage_class}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/storage_class",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/storage_class/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/storage_class/{k8s_storage_class}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/storage_class/{k8s_storage_class}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/storage_class/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_storage_class.settings"
 * )
 */
class K8sStorageClass extends K8sEntityBase implements K8sStorageClassInterface {

  /**
   * {@inheritdoc}
   */
  public function getParameters() {
    return $this->get('parameters')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setParameters($parameters) {
    return $this->set('parameters', $parameters);
  }

  /**
   * {@inheritdoc}
   */
  public function getProvisioner() {
    return $this->get('provisioner')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setProvisioner($provisioner) {
    return $this->set('provisioner', $provisioner);
  }

  /**
   * {@inheritdoc}
   */
  public function getReclaimPolicy() {
    return $this->get('reclaim_policy')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setReclaimPolicy($reclaim_policy) {
    return $this->set('reclaim_policy', $reclaim_policy);
  }

  /**
   * {@inheritdoc}
   */
  public function getVolumeBindingMode() {
    return $this->get('volume_binding_mode')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setVolumeBindingMode($volume_binding_mode) {
    return $this->set('volume_binding_mode', $volume_binding_mode);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['parameters'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Parameters'))
      ->setDescription(t('Parameters holds the parameters for the provisioner that should create volumes of this storage class.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    $fields['provisioner'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Provisioner'))
      ->setDescription(t('The type of the provisioner.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['reclaim_policy'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Reclaim Policy'))
      ->setDescription(t('Dynamically provisioned PersistentVolumes of this storage class are created with this reclaimPolicy. Defaults to Delete.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['volume_binding_mode'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Volume Binding Mode'))
      ->setDescription(t('Indicates how PersistentVolumeClaims should be provisioned and bound.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    return $fields;
  }

}
