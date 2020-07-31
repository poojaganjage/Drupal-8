<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the Service Account entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_service_account",
 *   id_plural = "k8s_service_accounts",
 *   label = @Translation("Service Account"),
 *   label_collection = @Translation("Service Accounts"),
 *   label_singular = @Translation("Service Account"),
 *   label_plural = @Translation("Service Accounts"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sServiceAccountViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sServiceAccountViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sServiceAccountAccessControlHandler",
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
 *   base_table = "k8s_service_account",
 *   admin_permission = "administer k8s service account",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/service_account/{k8s_service_account}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/service_account",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/service_account/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/service_account/{k8s_service_account}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/service_account/{k8s_service_account}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/service_account/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_service_account.settings"
 * )
 */
class K8sServiceAccount extends K8sEntityBase implements K8sServiceAccountInterface {

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
  public function getSecrets() {
    return $this->get('secrets');
  }

  /**
   * {@inheritdoc}
   */
  public function setSecrets($secrets) {
    return $this->set('secrets', $secrets);
  }

  /**
   * {@inheritdoc}
   */
  public function getImagePullSecrets() {
    return $this->get('image_pull_secrets');
  }

  /**
   * {@inheritdoc}
   */
  public function setImagePullSecrets($image_pull_secrets) {
    return $this->set('image_pull_secrets', $image_pull_secrets);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['namespace'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Namespace'))
      ->setDescription(t('The namespace of service account.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['secrets'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Secrets'))
      ->setDescription(t("Secrets is the list of secrets allowed to be used by pods running using this ServiceAccount."))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 4096)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    $fields['image_pull_secrets'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Image Pull Secrets'))
      ->setDescription(t("AutomountServiceAccountToken indicates whether pods running as this service account should have an API token automatically mounted."))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 4096)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    return $fields;
  }

}
