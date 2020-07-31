<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Stateful Set entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_stateful_set",
 *   id_plural = "k8s_stateful_sets",
 *   label = @Translation("Stateful Set"),
 *   label_collection = @Translation("Stateful Sets"),
 *   label_singular = @Translation("Stateful Set"),
 *   label_plural = @Translation("Stateful Sets"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sStatefulSetViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sStatefulSetViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sStatefulSetAccessControlHandler",
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
 *   base_table = "k8s_stateful_set",
 *   admin_permission = "administer k8s stateful set",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/stateful_set/{k8s_stateful_set}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/stateful_set",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/stateful_set/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/stateful_set/{k8s_stateful_set}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/stateful_set/{k8s_stateful_set}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/stateful_set/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_stateful_set.settings"
 * )
 */
class K8sStatefulSet extends K8sEntityBase implements K8sStatefulSetInterface {

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
  public function getUpdateStrategy() {
    return $this->get('update_strategy');
  }

  /**
   * {@inheritdoc}
   */
  public function setUpdateStrategy($update_strategy) {
    return $this->set('update_strategy', $update_strategy);
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionHistoryLimit() {
    return $this->get('revision_history_limit');
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionHistoryLimit($revision_history_limit) {
    return $this->set('revision_history_limit', $revision_history_limit);
  }

  /**
   * {@inheritdoc}
   */
  public function getServiceName() {
    return $this->get('service_name');
  }

  /**
   * {@inheritdoc}
   */
  public function setServiceName($service_name) {
    return $this->set('service_name', $service_name);
  }

  /**
   * {@inheritdoc}
   */
  public function getPodManagementPolicy() {
    return $this->get('pod_management_policy');
  }

  /**
   * {@inheritdoc}
   */
  public function setPodManagementPolicy($pod_management_policy) {
    return $this->set('pod_management_policy', $pod_management_policy);
  }

  /**
   * {@inheritdoc}
   */
  public function getCollisionCount() {
    return $this->get('collision_count');
  }

  /**
   * {@inheritdoc}
   */
  public function setCollisionCount($collision_count) {
    return $this->set('collision_count', $collision_count);
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
  public function getReadyReplicas() {
    return $this->get('ready_replicas')->value;
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
  public function getReplicas() {
    return $this->get('replicas')->value;
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
  public function getCurrentReplicas() {
    return $this->get('current_replicas');
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrentReplicas($current_replicas) {
    return $this->set('current_replicas', $current_replicas);
  }

  /**
   * {@inheritdoc}
   */
  public function getUpdatedReplicas() {
    return $this->get('updated_replicas');
  }

  /**
   * {@inheritdoc}
   */
  public function setUpdatedReplicas($updated_replicas) {
    return $this->set('updated_replicas', $updated_replicas);
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentRevision() {
    return $this->get('current_revision')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrentRevision($current_revision) {
    return $this->set('current_revision', $current_revision);
  }

  /**
   * {@inheritdoc}
   */
  public function getUpdateRevision() {
    return $this->get('update_revision')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setUpdateRevision($update_revision) {
    return $this->set('update_revision', $update_revision);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['namespace'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Namespace'))
      ->setDescription(t('The namespace of stateful set.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['update_strategy'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Update Strategy'))
      ->setDescription(t('updateStrategy indicates the StatefulSetUpdateStrategy that will be employed to update Pods in the StatefulSet when a revision is made to Template.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['revision_history_limit'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Revision History Limit'))
      ->setDescription(t("revisionHistoryLimit is the maximum number of revisions that will be maintained in the StatefulSet's revision history."))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['service_name'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Service Name'))
      ->setDescription(t('serviceName is the name of the service that governs this StatefulSet. This service must exist before the StatefulSet, and is responsible for the network identity of the set.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['pod_management_policy'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Pod Management Policy'))
      ->setDescription(t('podManagementPolicy controls how pods are created during initial scale up, when replacing pods on nodes, or when scaling down.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['collision_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Collision Count'))
      ->setDescription(t('collisionCount is the count of hash collisions for the StatefulSet. The StatefulSet controller uses this field as a collision avoidance mechanism when it needs to create the name for the newest ControllerRevision.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['observed_generation'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Observed Generation'))
      ->setDescription(t("observedGeneration is the most recent generation observed for this StatefulSet. It corresponds to the StatefulSet's generation, which is updated on mutation by the API Server."))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['ready_replicas'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Ready Replicas'))
      ->setDescription(t('readyReplicas is the number of Pods created by the StatefulSet controller that have a Ready Condition.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['replicas'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Replicas'))
      ->setDescription(t('replicas is the number of Pods created by the StatefulSet controller.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['current_replicas'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Current Replicas'))
      ->setDescription(t('currentReplicas is the number of Pods created by the StatefulSet controller from the StatefulSet version indicated by currentRevision.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['updated_replicas'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Updated Replicas'))
      ->setDescription(t('updatedReplicas is the number of Pods created by the StatefulSet controller from the StatefulSet version indicated by updateRevision.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['current_revision'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Current Revision'))
      ->setDescription(t('currentRevision, if not empty, indicates the version of the StatefulSet used to generate Pods in the sequence [0,currentReplicas).'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'pre_string_formatter',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['update_revision'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Updated Revision'))
      ->setDescription(t('updateRevision, if not empty, indicates the version of the StatefulSet used to generate Pods in the sequence [replicas-updatedReplicas,replicas)'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'pre_string_formatter',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    return $fields;
  }

}
