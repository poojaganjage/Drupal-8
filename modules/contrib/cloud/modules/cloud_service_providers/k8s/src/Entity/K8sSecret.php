<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the Secret entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_secret",
 *   id_plural = "k8s_secrets",
 *   label = @Translation("Secret"),
 *   label_collection = @Translation("Secrets"),
 *   label_singular = @Translation("Secret"),
 *   label_plural = @Translation("Secrets"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sSecretViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sSecretViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sSecretAccessControlHandler",
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
 *   base_table = "k8s_secret",
 *   admin_permission = "administer k8s secret",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/secret/{k8s_secret}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/secret",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/secret/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/secret/{k8s_secret}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/secret/{k8s_secret}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/secret/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_secret.settings"
 * )
 */
class K8sSecret extends K8sEntityBase implements K8sSecretInterface {

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
  public function getSecretType() {
    return $this->get('secret_type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSecretType($secret_type) {
    return $this->set('secret_type', $secret_type);
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
      ->setDescription(t('The namespace of secret.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['secret_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Type'))
      ->setDescription(t('Used to facilitate programmatic handling of secret data.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['data'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Data'))
      ->setDescription(t('Secret data.'))
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
