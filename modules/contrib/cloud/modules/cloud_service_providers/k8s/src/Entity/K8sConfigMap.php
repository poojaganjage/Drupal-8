<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the ConfigMap entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_config_map",
 *   id_plural = "k8s_config_maps",
 *   label = @Translation("ConfigMap"),
 *   label_collection = @Translation("ConfigMaps"),
 *   label_singular = @Translation("ConfigMap"),
 *   label_plural = @Translation("ConfigMaps"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sConfigMapViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sConfigMapViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sConfigMapAccessControlHandler",
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
 *   base_table = "k8s_config_map",
 *   admin_permission = "administer k8s configmap",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/config_map/{k8s_config_map}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/config_map",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/config_map/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/config_map/{k8s_config_map}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/config_map/{k8s_config_map}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/config_map/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_config_map.settings"
 * )
 */
class K8sConfigMap extends K8sEntityBase implements K8sConfigMapInterface {

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
  public function getData() {
    return $this->get('data')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setData($data) {
    return $this->set('data', $data);
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
      ->setDescription(t('The namespace of configmap.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['data'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Data'))
      ->setDescription(t('ConfigMap data.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 4096)
      ->setSetting('long', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
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
