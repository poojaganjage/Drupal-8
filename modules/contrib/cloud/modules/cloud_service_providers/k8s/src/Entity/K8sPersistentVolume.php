<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the persistent volume entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_persistent_volume",
 *   id_plural = "k8s_persistent_volumes",
 *   label = @Translation("Persistent Volume"),
 *   label_collection = @Translation("Persistent Volumes"),
 *   namespaceable = FALSE,
 *   label_singular = @Translation("Persistent Volume"),
 *   label_plural = @Translation("Persistent Volumes"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sPersistentVolumeViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sPersistentVolumeViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sPersistentVolumeAccessControlHandler",
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
 *   base_table = "k8s_persistent_volume",
 *   admin_permission = "administer k8s persistent volume",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/persistent_volume/{k8s_persistent_volume}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/persistent_volume",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/persistent_volume/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/persistent_volume/{k8s_persistent_volume}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/persistent_volume/{k8s_persistent_volume}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/persistent_volume/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_persistent_volume.settings"
 * )
 */
class K8sPersistentVolume extends K8sEntityBase implements K8sPersistentVolumeInterface {

  /**
   * {@inheritdoc}
   */
  public function getCapacity() {
    return $this->get('capacity')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCapacity($capacity) {
    return $this->set('capacity', $capacity);
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessModes() {
    return $this->get('access_modes')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAccessModes($access_modes) {
    return $this->set('access_modes', $access_modes);
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
  public function getStorageClassName() {
    return $this->get('storage_class_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStorageClassName($storage_class_name) {
    return $this->set('storage_class_name', $storage_class_name);
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
  public function setPhase($phase) {
    return $this->set('phase', $phase);
  }

  /**
   * {@inheritdoc}
   */
  public function getClaimRef() {
    return $this->get('claim_ref')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setClaimRef($claim_ref) {
    return $this->set('claim_ref', $claim_ref);
  }

  /**
   * {@inheritdoc}
   */
  public function getReason() {
    return $this->get('reason')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setReason($reason) {
    return $this->set('reason', $reason);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['capacity'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Capacity'))
      ->setDescription(t('Capacity.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['access_modes'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Access Modes'))
      ->setDescription(t('Access Modes.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['reclaim_policy'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Reclaim Policy'))
      ->setDescription(t('Reclaim Policy.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['storage_class_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Storage Class Name'))
      ->setDescription(t('Storage Class Name.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['phase'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Phase'))
      ->setDescription(t('Phase indicates if a volume is available, bound to a claim, or released by a claim.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['claim_ref'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Claim'))
      ->setDescription(t('ClaimRef is part of a bi-directional binding between PersistentVolume and PersistentVolumeClaim.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['reason'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Reason'))
      ->setDescription(t('Reason is a brief CamelCase string that describes any failure and is meant for machine parsing and tidy display in the CLI.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    return $fields;
  }

}
