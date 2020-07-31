<?php

namespace Drupal\cloud\Entity;

use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Provides the cloud base entity view builders.
 */
abstract class CloudViewBuilder extends EntityViewBuilder {

  use CloudContentEntityTrait;

  /**
   * Get definitions of fieldsets.
   *
   * @return array
   *   Definitions of fieldsets.
   */
  abstract protected function getFieldsetDefs();

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $weight = -100;
    $build = [];

    // Default parameters.
    $default_params = [];
    foreach ($this->getFieldsetDefs() ?: [] as $fieldset_def) {
      foreach ($fieldset_def['fields'] ?: [] as $field_name) {
        $default_params[$field_name] = NULL;
      }
    }

    foreach ($this->getFieldsetDefs() ?: [] as $fieldset_def) {
      $fieldset_name = $fieldset_def['name'];
      $build[$fieldset_name] = [
        '#type' => 'details',
        '#title' => $fieldset_def['title'],
        '#weight' => $weight++,
        '#tree' => TRUE,
        '#open' => $fieldset_def['open'],
      ];

      $params = $default_params;
      foreach ($fieldset_def['fields'] ?: [] as $field_name) {
        $params[$field_name] = $this->getFieldValue(
          $entity,
          $field_name,
          $entity->get($field_name)
        );
      }

      $new_entity = $entity->create($params);
      $build[$fieldset_name][] = parent::view($new_entity, $view_mode, $langcode);
    }

    $build += parent::view($entity, $view_mode, $langcode);
    $build['#pre_render'][] = [$this, 'removeFieldsExceptLabelField'];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return array_merge(parent::trustedCallbacks(), ['removeFieldsExceptLabelField']);
  }

  /**
   * Remove all fields except the label field which is used to build title.
   *
   * @param array $build
   *   A renderable array containing build information and context for an entity
   *   view.
   *
   * @return array
   *   The updated renderable array.
   */
  public function removeFieldsExceptLabelField(array $build) {
    $item_names = array_column($this->getFieldsetDefs(), 'name');

    $entity_type = $build['#entity_type'];
    $entity = $build['#' . $entity_type];
    $label_field = $entity->getEntityType()->getKey('label');

    $item_names[] = $label_field;

    return array_filter($build, static function ($k) use ($item_names) {
      if (strpos($k, '#') === 0) {
        return TRUE;
      }
      return in_array($k, $item_names);
    }, ARRAY_FILTER_USE_KEY);

  }

  /**
   * Get the value of the field.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string $field_name
   *   The name of the field.
   * @param mixed $default_value
   *   The default value of the field.
   *
   * @return mixed
   *   The value of the field.
   */
  protected function getFieldValue(EntityInterface $entity, $field_name, $default_value) {
    return $default_value;
  }

}
