<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Priority Class entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_priority_class",
 *   id_plural = "k8s_priority_classes",
 *   label = @Translation("Priority Class"),
 *   label_collection = @Translation("Priority Classes"),
 *   label_singular = @Translation("Priority Class"),
 *   label_plural = @Translation("Priority Classes"),
 *   namespaceable = FALSE,
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sPriorityClassViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sPriorityClassViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sPriorityClassAccessControlHandler",
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
 *   base_table = "k8s_priority_class",
 *   admin_permission = "administer k8s priority class",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/priority_class/{k8s_priority_class}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/priority_class",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/priority_class/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/priority_class/{k8s_priority_class}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/priority_class/{k8s_priority_class}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/priority_class/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_priority_class.settings"
 * )
 */
class K8sPriorityClass extends K8sEntityBase implements K8sPriorityClassInterface {

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    return $this->get('value')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($value) {
    return $this->set('value', $value);
  }

  /**
   * {@inheritdoc}
   */
  public function getGlobalDefault() {
    return $this->get('global_default')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setGlobalDefault($global_default) {
    return $this->set('global_default', $global_default);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->get('description')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    return $this->set('description', $description);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['value'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Value'))
      ->setDescription(t('Value indicates the pod priority. The higher the value, the higher the priority.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['global_default'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Global Default'))
      ->setDescription(t('GlobalDefault indicates that the value of this PriorityClass
       should be used for Pods without a priorityClassName. Only one PriorityClass
        with globalDefault set to true can exist in the system.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'boolean',
        'weight' => -5,
        'settings' => [
          'format' => 'true-false',
        ],
      ]);

    $fields['description'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Description'))
      ->setDescription(t('Description tell users of the cluster when they should use this PriorityClass.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    return $fields;
  }

}
