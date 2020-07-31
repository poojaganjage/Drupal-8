<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the Endpoint entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_endpoint",
 *   id_plural = "k8s_endpoints",
 *   label = @Translation("Endpoint"),
 *   label_collection = @Translation("Endpoints"),
 *   label_singular = @Translation("Endpoint"),
 *   label_plural = @Translation("Endpoints"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sEndpointViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sEndpointViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sEndpointAccessControlHandler",
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
 *   base_table = "k8s_endpoint",
 *   admin_permission = "administer k8s endpoint",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/endpoint/{k8s_endpoint}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/endpoint",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/endpoint/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/endpoint/{k8s_endpoint}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/endpoint/{k8s_endpoint}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/endpoint/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_endpoint.settings"
 * )
 */
class K8sEndpoint extends K8sEntityBase implements K8sEndpointInterface {

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
  public function getNodeName() {
    return $this->get('node_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setNodeName($node_name) {
    return $this->set('node_name', $node_name);
  }

  /**
   * {@inheritdoc}
   */
  public function getAddresses() {
    return $this->get('addresses')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAddresses(array $addresses) {
    return $this->set('addresses', $addresses);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['namespace'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Namespace'))
      ->setDescription(t('The namespace of endpoint.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['node_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Node'))
      ->setDescription(t('NodeName is a request to schedule this endpoint onto a specific node.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['addresses'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Addresses'))
      ->setDescription(t('List of addresses.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    return $fields;
  }

}
