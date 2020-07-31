<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the K8s Namespace entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_namespace",
 *   id_plural = "k8s_namespaces",
 *   label = @Translation("Namespace"),
 *   label_collection = @Translation("Namespaces"),
 *   label_singular = @Translation("Namespace"),
 *   label_plural = @Translation("Namespaces"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sNamespaceViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sNamespaceViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sNamespaceAccessControlHandler",
 *     "form" = {
 *       "add"        = "Drupal\k8s\Form\K8sNamespaceCreateForm",
 *       "edit"       = "Drupal\k8s\Form\K8sNamespaceEditForm",
 *       "delete"     = "Drupal\k8s\Form\K8sNamespaceDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\k8s\Form\K8sNamespaceDeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "k8s_namespace",
 *   admin_permission = "administer k8s namespace",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/namespace/{k8s_namespace}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/namespace",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/namespace/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/namespace/{k8s_namespace}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/namespace/{k8s_namespace}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/namespace/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_namespace.settings"
 * )
 */
class K8sNamespace extends K8sEntityBase implements K8sNamespaceInterface {

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus($status) {
    return $this->set('status', $status);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['labels']
      ->setDisplayOptions('form', [
        'type' => 'key_value_item',
      ]);

    $fields['annotations']
      ->setDisplayOptions('form', [
        'type' => 'key_value_item',
      ]);

    $fields['status'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Status'))
      ->setDescription(t('The status of k8s namespace.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['detail'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Detail'))
      ->setDescription(t('Entity detail.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'pre_string_formatter',
        'weight' => -5,
      ])
      ->addConstraint('yaml_array_data');

    return $fields;
  }

}
