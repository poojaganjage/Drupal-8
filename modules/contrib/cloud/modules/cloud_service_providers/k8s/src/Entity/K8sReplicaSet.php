<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the ReplicaSet entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_replica_set",
 *   id_plural = "k8s_replica_sets",
 *   label = @Translation("Replica Set"),
 *   label_collection = @Translation("Replica Sets"),
 *   label_singular = @Translation("Replica Set"),
 *   label_plural = @Translation("Replica Sets"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sReplicaSetViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sReplicaSetViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sReplicaSetAccessControlHandler",
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
 *   base_table = "k8s_replica_set",
 *   admin_permission = "administer k8s replica set",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/replica_set/{k8s_replica_set}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/replica_set",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/replica_set/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/replica_set/{k8s_replica_set}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/replica_set/{k8s_replica_set}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/replica_set/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_replica_set.settings"
 * )
 */
class K8sReplicaSet extends K8sEntityBase implements K8sReplicaSetInterface {

  /**
   * {@inheritdoc}
   */
  public function getNamespace() {
    return $this->get('namespace')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setNamespace($namespace) {
    return $this->set('namespace', $namespace);
  }

  /**
   * {@inheritdoc}
   */
  public function getReplicas() {
    return $this->get('replicas');
  }

  /**
   * {@inheritdoc}
   */
  public function setReplicas($replicas) {
    return $this->set('replicas', $replicas);
  }

  /**
   * {@inheritdoc}
   */
  public function getSelector() {
    return $this->get('selector');
  }

  /**
   * {@inheritdoc}
   */
  public function setSelector($selector) {
    return $this->set('selector', $selector);
  }

  /**
   * {@inheritdoc}
   */
  public function getTemplate() {
    return $this->get('template');
  }

  /**
   * {@inheritdoc}
   */
  public function setTemplate($template) {
    return $this->set('template', $template);
  }

  /**
   * {@inheritdoc}
   */
  public function getConditions() {
    return $this->get('conditions');
  }

  /**
   * {@inheritdoc}
   */
  public function setConditions($conditions) {
    return $this->set('conditions', $conditions);
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableReplicas() {
    return $this->get('available_replicas');
  }

  /**
   * {@inheritdoc}
   */
  public function setAvailableReplicas($available_replicas) {
    return $this->set('available_replicas', $available_replicas);
  }

  /**
   * {@inheritdoc}
   */
  public function getFullyLabeledReplicas() {
    return $this->get('fully_labeled_replicas');
  }

  /**
   * {@inheritdoc}
   */
  public function setFullyLabeledReplicas($fully_labeled_replicas) {
    return $this->set('fully_labeled_replicas', $fully_labeled_replicas);
  }

  /**
   * {@inheritdoc}
   */
  public function getReadyReplicas() {
    return $this->get('ready_replicas');
  }

  /**
   * {@inheritdoc}
   */
  public function setReadyReplicas($ready_replicas) {
    return $this->set('ready_replicas', $ready_replicas);
  }

  /**
   * {@inheritdoc}
   */
  public function getObservedGeneration() {
    return $this->get('observed_generation');
  }

  /**
   * {@inheritdoc}
   */
  public function setObservedGeneration($observed_generation) {
    return $this->set('observed_generation', $observed_generation);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['namespace'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Namespace'))
      ->setDescription(t('The namespace of replica set.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['replicas'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Replicas'))
      ->setDescription(t('Total number of non-terminated pods on manifest.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['selector'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Selector'))
      ->setDescription(t('Label selector on manifest.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['template'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Template'))
      ->setDescription(t('Template used for pod.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['available_replicas'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Available Replicas'))
      ->setDescription(t('The number of available replicas (ready for at least minReadySeconds) for this replica set.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['fully_labeled_replicas'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Fully Labeled Replicas'))
      ->setDescription(t('The number of pods that have labels matching the labels of the pod template of the replicaset.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['conditions'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Conditions'))
      ->setDescription(t('Represents the latest available observations of  current state of a replica set.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['observed_generation'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Observed Generation'))
      ->setDescription(t('ObservedGeneration reflects the generation of the most recently observed ReplicaSet.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['ready_replicas'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Ready Replicas'))
      ->setDescription(t('The number of ready replicas for this replica set.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    return $fields;
  }

}
