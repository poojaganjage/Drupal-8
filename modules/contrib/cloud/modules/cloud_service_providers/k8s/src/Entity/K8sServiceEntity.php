<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the Service entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_service",
 *   id_plural = "k8s_services",
 *   label = @Translation("Service"),
 *   label_collection = @Translation("Services"),
 *   label_singular = @Translation("Service"),
 *   label_plural = @Translation("Services"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sServiceViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sServiceViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sServiceAccessControlHandler",
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
 *   base_table = "k8s_service",
 *   admin_permission = "administer k8s service",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/service/{k8s_service}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/service",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/service/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/service/{k8s_service}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/service/{k8s_service}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/service/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_service.settings"
 * )
 */
class K8sServiceEntity extends K8sEntityBase implements K8sServiceEntityInterface {

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
  public function getType() {
    return $this->get('type');
  }

  /**
   * {@inheritdoc}
   */
  public function setType($type) {
    return $this->set('type', $type);
  }

  /**
   * {@inheritdoc}
   */
  public function getSessionAffinity() {
    return $this->get('session_affinity');
  }

  /**
   * {@inheritdoc}
   */
  public function setSessionAffinity($session_affinity) {
    return $this->set('session_affinity', $session_affinity);
  }

  /**
   * {@inheritdoc}
   */
  public function getClusterIp() {
    return $this->get('cluster_ip');
  }

  /**
   * {@inheritdoc}
   */
  public function setClusterIp($cluster_ip) {
    return $this->set('cluster_ip', $cluster_ip);
  }

  /**
   * {@inheritdoc}
   */
  public function getInternalEndpoints() {
    return $this->get('internal_endpoints');
  }

  /**
   * {@inheritdoc}
   */
  public function setInternalEndpoints($internal_endpoints) {
    return $this->set('internal_endpoints', $internal_endpoints);
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalEndpoints() {
    return $this->get('external_endpoints');
  }

  /**
   * {@inheritdoc}
   */
  public function setExternalEndpoints($external_endpoints) {
    return $this->set('external_endpoints', $external_endpoints);
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
      ->setDescription(t('The namespace of service.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['selector'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Selector'))
      ->setDescription(t('Route service traffic to pods with label keys and values matching this selector.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 4096)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    $fields['type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Type'))
      ->setDescription(t('Determines how the Service is exposed.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['session_affinity'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Session Affinity'))
      ->setDescription(t('Supports "ClientIP" and "None". Used to maintain session affinity.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['cluster_ip'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Cluster IP'))
      ->setDescription(t('ClusterIP is the IP address of the service and is usually assigned randomly by the master.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['internal_endpoints'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Internal Endpoints'))
      ->setDescription(t('Internal endpoints.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['external_endpoints'] = BaseFieldDefinition::create('string')
      ->setLabel(t('External Endpoints'))
      ->setDescription(t('External endpoints.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

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
