<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the API Serrvice entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_api_service",
 *   id_plural = "k8s_api_services",
 *   label = @Translation("API Service"),
 *   label_collection = @Translation("API Services"),
 *   label_singular = @Translation("API Service"),
 *   label_plural = @Translation("API Services"),
 *   namespaceable = FALSE,
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sApiServiceViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sApiServiceViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sApiServiceAccessControlHandler",
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
 *   base_table = "k8s_api_service",
 *   admin_permission = "administer k8s Api service",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/api_service/{k8s_api_service}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/api_service",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/api_service/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/api_service/{k8s_api_service}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/api_service/{k8s_api_service}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/api_service/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_api_service.settings"
 * )
 */
class K8sApiService extends K8sEntityBase implements K8sApiServiceInterface {

  /**
   * {@inheritdoc}
   */
  public function getGroupPriorityMinimum() {
    return $this->get('group_priority_minimum')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setGroupPriorityMinimum($group_priority_minimum) {
    return $this->set('group_priority_minimum', $group_priority_minimum);
  }

  /**
   * {@inheritdoc}
   */
  public function getService() {
    return $this->get('service')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setService($service) {
    return $this->set('service', $service);
  }

  /**
   * {@inheritdoc}
   */
  public function getVersionPriority() {
    return $this->get('version_priority')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setVersionPriority($version_priority) {
    return $this->set('version_priority', $version_priority);
  }

  /**
   * {@inheritdoc}
   */
  public function getConditions() {
    return $this->get('conditions')->value;
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
  public function getGroup() {
    return $this->get('group')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setGroup($group) {
    return $this->set('group', $group);
  }

  /**
   * {@inheritdoc}
   */
  public function getInsecureSkipTlsVerify() {
    return $this->get('insecure_skip_tls_verify')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setInsecureSkipTlsVerify($insecure_skip_tls_verify) {
    return $this->set('insecure_skip_tls_verify', $insecure_skip_tls_verify);
  }

  /**
   * {@inheritdoc}
   */
  public function getVersion() {
    return $this->get('version')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setVersion($version) {
    return $this->set('version', $version);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['group_priority_minimum'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Group Priority Minimum'))
      ->setDescription(t('GroupPriorityMininum is the priority this group should have at least.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['service'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Service'))
      ->setDescription(t('Service is a reference to the service for this API server.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    $fields['version_priority'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Version Priority'))
      ->setDescription(t('Version is the API version this server hosts.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['conditions'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Conditions'))
      ->setDescription(t('Conditions.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    $fields['group'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Group'))
      ->setDescription(t('Group is the API group name this server hosts.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['insecure_skip_tls_verify'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('InsecureSkipTlsVerify'))
      ->setDescription(t('InsecureSkipTLSVerify disables TLS certificate verification when communicating with this server.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'boolean',
        'weight' => -5,
        'settings' => [
          'format' => 'true-false',
        ],
      ]);

    $fields['version'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Version'))
      ->setDescription(t('Version is the API version this server hosts.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    return $fields;
  }

}
