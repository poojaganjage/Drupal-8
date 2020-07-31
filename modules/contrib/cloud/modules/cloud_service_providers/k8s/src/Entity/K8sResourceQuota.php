<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the Resource Quota entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_resource_quota",
 *   id_plural = "k8s_resource_quotas",
 *   label = @Translation("Resource Quota"),
 *   label_collection = @Translation("Resource Quotas"),
 *   label_singular = @Translation("Resource Quota"),
 *   label_plural = @Translation("Resource Quotas"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sResourceQuotaViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sResourceQuotaViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sResourceQuotaAccessControlHandler",
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
 *   base_table = "k8s_resource_quota",
 *   admin_permission = "administer k8s resource quota",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/resource_quota/{k8s_resource_quota}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/resource_quota",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/resource_quota/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/resource_quota/{k8s_resource_quota}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/resource_quota/{k8s_resource_quota}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/resource_quota/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_resource_quota.settings"
 * )
 */
class K8sResourceQuota extends K8sEntityBase implements K8sResourceQuotaInterface {

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
  public function getStatusHard() {
    return $this->get('status_hard');
  }

  /**
   * {@inheritdoc}
   */
  public function setStatusHard($status_hard) {
    return $this->set('status_hard', $status_hard);
  }

  /**
   * {@inheritdoc}
   */
  public function getStatusUsed() {
    return $this->get('status_used');
  }

  /**
   * {@inheritdoc}
   */
  public function setStatusUsed($status_used) {
    return $this->set('status_used', $status_used);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['namespace'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Namespace'))
      ->setDescription(t('The namespace of resource quota.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['status_hard'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Hard (status)'))
      ->setDescription(t('Hard is the set of enforced hard limits for each named resource.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    $fields['status_used'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Used (status)'))
      ->setDescription(t('Used is the current observed total usage of the resource in the namespace.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    return $fields;
  }

}
