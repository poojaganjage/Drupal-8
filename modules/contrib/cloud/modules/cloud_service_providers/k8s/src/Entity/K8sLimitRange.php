<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the Limit Range entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_limit_range",
 *   id_plural = "k8s_limit_ranges",
 *   label = @Translation("Limit Range"),
 *   label_collection = @Translation("Limit Ranges"),
 *   label_singular = @Translation("Limit Range"),
 *   label_plural = @Translation("Limit Ranges"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sLimitRangeViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sLimitRangeViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sLimitRangeAccessControlHandler",
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
 *   base_table = "k8s_limit_range",
 *   admin_permission = "administer k8s limit range",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/limit_range/{k8s_limit_range}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/limit_range",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/limit_range/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/limit_range/{k8s_limit_range}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/limit_range/{k8s_limit_range}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/limit_range/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_limit_range.settings"
 * )
 */
class K8sLimitRange extends K8sEntityBase implements K8sLimitRangeInterface {

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
  public function getLimits() {
    return $this->get('limits');
  }

  /**
   * {@inheritdoc}
   */
  public function setLimits($limits) {
    return $this->set('limits', $limits);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['namespace'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Namespace'))
      ->setDescription(t('The namespace of limit range.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['limits'] = BaseFieldDefinition::create('limit')
      ->setLabel(t('Limits'))
      ->setDescription(t('The limits.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'type' => 'limit_formatter',
        'weight' => -5,
      ]);

    return $fields;
  }

}
