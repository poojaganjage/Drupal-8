<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Persistent Volume Claim entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_persistent_volume_claim",
 *   id_plural = "k8s_persistent_volume_claims",
 *   label = @Translation("Persistent Volume Claim"),
 *   label_collection = @Translation("Persistent Volume Claims"),
 *   label_singular = @Translation("Persistent Volume Claim"),
 *   label_plural = @Translation("Persistent Volume Claims"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sPersistentVolumeClaimViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sPersistentVolumeClaimViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sPersistentVolumeClaimAccessControlHandler",
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
 *   base_table = "k8s_persistent_volume_claim",
 *   admin_permission = "administer k8s persistent volume claim",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/persistent_volume_claim/{k8s_persistent_volume_claim}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/persistent_volume_claim",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/persistent_volume_claim/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/persistent_volume_claim/{k8s_persistent_volume_claim}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/persistent_volume_claim/{k8s_persistent_volume_claim}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/persistent_volume_claim/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_persistent_volume_claim.settings"
 * )
 */
class K8sPersistentVolumeClaim extends K8sEntityBase implements K8sPersistentVolumeClaimInterface {

  /**
   * {@inheritdoc}
   */
  public function getNamespace() {
    return $this->get('namespace')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setNamespace($val) {
    return $this->set('namespace', $val);
  }

  /**
   * {@inheritdoc}
   */
  public function getPhase() {
    return $this->get('phase')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPhase($val) {
    return $this->set('phase', $val);
  }

  /**
   * {@inheritdoc}
   */
  public function getVolumeName() {
    return $this->get('volume_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setVolumeName($val) {
    return $this->set('volume_name', $val);
  }

  /**
   * {@inheritdoc}
   */
  public function getCapacity() {
    return $this->get('capacity')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCapacity($val) {
    return $this->set('capacity', $val);
  }

  /**
   * {@inheritdoc}
   */
  public function getRequest() {
    return $this->get('request')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRequest($val) {
    return $this->set('request', $val);
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessMode() {
    return $this->get('access_mode')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAccessMode($val) {
    return $this->set('access_mode', $val);
  }

  /**
   * {@inheritdoc}
   */
  public function getStorageClass() {
    return $this->get('storage_class')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStorageClass($val) {
    return $this->set('storage_class', $val);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['namespace'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Namespace'))
      ->setDescription(t('The namespace of persistent volume claim.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['phase'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Phase'))
      ->setDescription(t('The status phase of persistent volume claim.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['volume_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('VolumeName'))
      ->setDescription(t('The volume name of persistent volume claim.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['capacity'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Capacity'))
      ->setDescription(t('The capacity storage of persistent volume claim.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['request'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Request'))
      ->setDescription(t('The request storage of persistent volume claim.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['access_mode'] = BaseFieldDefinition::create('string')
      ->setLabel(t('AccessMode'))
      ->setDescription(t('The access mode of persistent volume claim.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['storage_class'] = BaseFieldDefinition::create('string')
      ->setLabel(t('StorageClass'))
      ->setDescription(t('The storage class of persistent volume claim.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    return $fields;
  }

}
