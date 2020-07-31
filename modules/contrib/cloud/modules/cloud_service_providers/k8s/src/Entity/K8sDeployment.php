<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Deployment entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_deployment",
 *   id_plural = "k8s_deployments",
 *   label = @Translation("Deployment"),
 *   label_collection = @Translation("Deployments"),
 *   label_singular = @Translation("Deployment"),
 *   label_plural = @Translation("Deployments"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sDeploymentViewBuilder",
 *     "list_builder" = "Drupal\k8s\Controller\K8sListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sDeploymentViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sDeploymentAccessControlHandler",
 *     "form" = {
 *       "add"        = "Drupal\k8s\Form\K8sCreateForm",
 *       "edit"       = "Drupal\k8s\Form\K8sEditForm",
 *       "delete"     = "Drupal\k8s\Form\K8sDeleteForm",
 *       "scale"      = "Drupal\k8s\Form\K8sDeploymentScaleForm",
 *       "delete-multiple-confirm" = "Drupal\k8s\Form\K8sDeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "k8s_deployment",
 *   admin_permission = "administer k8s deployment",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/deployment/{k8s_deployment}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/deployment",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/deployment/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/deployment/{k8s_deployment}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/deployment/{k8s_deployment}/delete",
 *     "scale-form"           = "/clouds/k8s/{cloud_context}/deployment/{k8s_deployment}/scale",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/deployment/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_deployment.settings"
 * )
 */
class K8sDeployment extends K8sEntityBase implements K8sDeploymentInterface {

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
  public function getStrategy() {
    return $this->get('strategy');
  }

  /**
   * {@inheritdoc}
   */
  public function setStrategy($strategy) {
    return $this->set('strategy', $strategy);
  }

  /**
   * {@inheritdoc}
   */
  public function getMinReadySeconds() {
    return $this->get('min_ready_seconds');
  }

  /**
   * {@inheritdoc}
   */
  public function setMinReadySeconds($min_ready_seconds) {
    return $this->set('min_ready_seconds', $min_ready_seconds);
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
  public function getUnavailableReplicas() {
    return $this->get('unavailable_replicas');
  }

  /**
   * {@inheritdoc}
   */
  public function setUnavailableReplicas($unavailable_replicas) {
    return $this->set('unavailable_replicas', $unavailable_replicas);
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
  public function getCreationYaml() {
    return $this->get('creation_yaml')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreationYaml($creation_yaml) {
    return $this->set('creation_yaml', $creation_yaml);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['namespace'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Namespace'))
      ->setDescription(t('The namespace of deployment.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['strategy'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Strategy'))
      ->setDescription(t('The deployment strategy to use to replace existing pods with new ones.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['min_ready_seconds'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Minimum ready seconds'))
      ->setDescription(t('Minimum number of seconds for which a newly created pod should be ready without any of its container crashing, for it to be considered available.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['revision_history_limit'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Revision History Limit'))
      ->setDescription(t('The number of old ReplicaSets to retain to allow rollback. This is a pointer to distinguish between explicit zero and not specified.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['available_replicas'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Available Replicas'))
      ->setDescription(t('Total number of available pods (ready for at least minReadySeconds) targeted by this deployment.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['collision_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Collision Count'))
      ->setDescription(t('Count of hash collisions for the Deployment. The Deployment controller uses this field as a collision avoidance mechanism when it needs to create the name for the newest ReplicaSet.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['observed_generation'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Observed Generation'))
      ->setDescription(t('The generation observed by the deployment controller.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['ready_replicas'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Ready Replicas'))
      ->setDescription(t('Total number of ready pods targeted by this deployment.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['replicas'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Replicas'))
      ->setDescription(t('Total number of non-terminated pods targeted by this deployment (their labels match the selector).'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['unavailable_replicas'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Unavailable Replicas'))
      ->setDescription(t('Total number of unavailable pods targeted by this deployment.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['updated_replicas'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Updated Replicas'))
      ->setDescription(t('Total number of non-terminated pods targeted by this deployment that have the desired template spec.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['creation_yaml'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Creation YAML'))
      ->setDescription(t('The YAML content was used to create the entity.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'pre_string_formatter',
        'weight' => -5,
      ]);

    return $fields;
  }

}
