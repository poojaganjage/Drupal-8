<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the Cluster Role Binding entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_cluster_role_binding",
 *   id_plural = "k8s_cluster_role_bindings",
 *   label = @Translation("Cluster Role Binding"),
 *   label_collection = @Translation("Cluster Role Bindings"),
 *   label_singular = @Translation("Cluster Role Binding"),
 *   label_plural = @Translation("Cluster Role Bindings"),
 *   namespaceable = FALSE,
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sClusterRoleBindingViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sClusterRoleBindingViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sClusterRoleBindingAccessControlHandler",
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
 *   base_table = "k8s_cluster_role_binding",
 *   admin_permission = "administer k8s cluster role binding",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/cluster_role_binding/{k8s_cluster_role_binding}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/cluster_role_binding",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/cluster_role_binding/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/cluster_role_binding/{k8s_cluster_role_binding}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/cluster_role_binding/{k8s_cluster_role_binding}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/cluster_role_binding/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_cluster_role_binding.settings"
 * )
 */
class K8sClusterRoleBinding extends K8sEntityBase implements K8sClusterRoleBindingInterface {

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
  public function getRoleRef() {
    return $this->get('role_ref')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRoleRef($role_ref) {
    return $this->set('role_ref', $role_ref);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['subjects'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Subjects'))
      ->setDescription(t('Subjects holds references to the objects the role applies to.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    $fields['role_ref'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Role'))
      ->setDescription(t('RoleRef can only reference a ClusterRole in the global namespace. If the RoleRef cannot be resolved, the Authorizer must return an error.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    return $fields;
  }

}
