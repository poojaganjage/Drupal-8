<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the Role Binding entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_role_binding",
 *   id_plural = "k8s_role_bindings",
 *   label = @Translation("Role Binding"),
 *   label_collection = @Translation("Role Bindings"),
 *   label_singular = @Translation("Role Binding"),
 *   label_plural = @Translation("Role Bindings"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sRoleBindingViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sRoleBindingViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sRoleBindingAccessControlHandler",
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
 *   base_table = "k8s_role_binding",
 *   admin_permission = "administer k8s role binding",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/role_binding/{k8s_role_binding}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/role_binding",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/role_binding/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/role_binding/{k8s_role_binding}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/role_binding/{k8s_role_binding}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/role_binding/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_role_binding.settings"
 * )
 */
class K8sRoleBinding extends K8sEntityBase implements K8sRoleBindingInterface {

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
  public function getRole() {
    return $this->get('role_ref')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRole($role_ref) {
    return $this->set('role_ref', $role_ref);
  }

  /**
   * {@inheritdoc}
   */
  public function getSubjects() {
    return $this->get('subjects')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubjects($subjects) {
    return $this->set('subjects', $subjects);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['namespace'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Namespace'))
      ->setDescription(t('The namespace of Role Binding.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['role_ref'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Role'))
      ->setDescription(t('RoleRef can reference a Role in the current namespace or a ClusterRole in the global namespace.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['subjects'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Subjects'))
      ->setDescription(t("Subjects holds references to the objects the role applies to."))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 4096)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    return $fields;
  }

}
