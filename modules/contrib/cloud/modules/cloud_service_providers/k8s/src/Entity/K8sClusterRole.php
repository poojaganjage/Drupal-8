<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the Cluster Role entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_cluster_role",
 *   id_plural = "k8s_cluster_roles",
 *   label = @Translation("Cluster Role"),
 *   label_collection = @Translation("Cluster Roles"),
 *   label_singular = @Translation("Cluster Role"),
 *   label_plural = @Translation("Cluster Roles"),
 *   namespaceable = FALSE,
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sClusterRoleViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sClusterRoleViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sClusterRoleAccessControlHandler",
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
 *   base_table = "k8s_cluster_role",
 *   admin_permission = "administer k8s cluster role",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/cluster_role/{k8s_cluster_role}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/cluster_role",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/cluster_role/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/cluster_role/{k8s_cluster_role}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/cluster_role/{k8s_cluster_role}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/cluster_role/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_cluster_role.settings"
 * )
 */
class K8sClusterRole extends K8sEntityBase implements K8sClusterRoleInterface {

  /**
   * {@inheritdoc}
   */
  public function getRules() {
    return $this->get('rules')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRules($rules) {
    return $this->set('rules', $rules);
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

    $fields['rules'] = BaseFieldDefinition::create('rule')
      ->setLabel(t('Rules'))
      ->setDescription(t('Rules.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 4096)
      ->setSetting('long', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'rule_formatter',
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
