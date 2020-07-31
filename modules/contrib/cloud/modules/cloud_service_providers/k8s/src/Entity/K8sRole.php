<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the Role entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_role",
 *   id_plural = "k8s_roles",
 *   label = @Translation("Role"),
 *   label_collection = @Translation("Roles"),
 *   label_singular = @Translation("Role"),
 *   label_plural = @Translation("Roles"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sRoleViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sRoleViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sRoleAccessControlHandler",
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
 *   base_table = "k8s_role",
 *   admin_permission = "administer k8s role",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/role/{k8s_role}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/role",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/role/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/role/{k8s_role}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/role/{k8s_role}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/role/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_role.settings"
 * )
 */
class K8sRole extends K8sEntityBase implements K8sRoleInterface {

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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['namespace'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Namespace'))
      ->setDescription(t('The namespace of role.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

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

    return $fields;
  }

}
