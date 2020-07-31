<?php

namespace Drupal\k8s\Traits;

use Drupal\cloud\Entity\CloudContentEntityBase;

/**
 * The trait for K8s Form.
 */
trait K8sFormTrait {

  /**
   * Get the entity type name with the format underscore.
   *
   * @param \Drupal\cloud\Entity\CloudContentEntityBase $entity
   *   The entity.
   *
   * @return string
   *   The name with the format underscore.
   */
  protected function getShortEntityTypeNameUnderscore(CloudContentEntityBase $entity) {
    $entity_type_id = $entity->getEntityTypeId();
    return substr($entity_type_id, strlen('k8s_'));
  }

  /**
   * Get the entity type name with the format whitespace.
   *
   * @param \Drupal\cloud\Entity\CloudContentEntityBase $entity
   *   The entity.
   *
   * @return string
   *   The name with the format whitespace.
   */
  protected function getShortEntityTypeNameWhitespace(CloudContentEntityBase $entity) {
    $entity_type_id = $entity->getEntityTypeId();
    $short_name = substr($entity_type_id, strlen('k8s_'));
    return ucwords(str_replace('_', ' ', $short_name));
  }

  /**
   * Get the entity type name with the format camel.
   *
   * @param \Drupal\cloud\Entity\CloudContentEntityBase $entity
   *   The entity.
   *
   * @return string
   *   The name with the format camel.
   */
  protected function getShortEntityTypeNameCamel(CloudContentEntityBase $entity) {
    return str_replace(' ', '', $this->getShortEntityTypeNameWhitespace($entity));
  }

  /**
   * Get the entity type name plural with the format camel.
   *
   * @param \Drupal\cloud\Entity\CloudContentEntityBase $entity
   *   The entity.
   *
   * @return string
   *   The name plural with the format camel.
   */
  protected function getShortEntityTypeNamePluralCamel(CloudContentEntityBase $entity) {
    $entity_type_id_plural = !empty($entity->getEntityType()) ? $entity->getEntityType()->get('id_plural') : '';
    $short_name = '';
    if (!empty($entity_type_id_plural)) {
      $short_name = substr($entity_type_id_plural, strlen('k8s_'));
    }
    return str_replace(' ', '', ucwords(str_replace('_', ' ', $short_name)));
  }

}
