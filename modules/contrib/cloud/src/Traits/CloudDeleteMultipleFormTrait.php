<?php

namespace Drupal\cloud\Traits;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Core\Url;

/**
 * The trait for CloudDeleteMultipleForm.
 *
 * See also Drupal\Core\Entity\EntityDeleteFormTrait.
 */
trait CloudDeleteMultipleFormTrait {

  // Don't use EntityDeleteFormTrait since it is an abstract class and requires
  // to implement a getEntity() method.
  use CloudContentEntityTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {

    // Get entity type ID from the route because ::buildForm has not yet been
    // called.
    $entity_type_id = $this->getRouteMatch()->getParameter('entity_type_id');
    return $entity_type_id . '_delete_multiple_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {

    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {

    return $this->formatPlural(count($this->selection),
      'Are you sure you want to delete this @item?',
      'Are you sure you want to delete these @items?', [
        '@item' => $this->entityType->getSingularLabel(),
        '@items' => $this->entityType->getPluralLabel(),
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {

    $route = \Drupal::routeMatch();
    $cloud_context = $route->getParameter('cloud_context');
    return new Url(
      'entity.' . $this->entityTypeId . '.collection',
      ['cloud_context' => $cloud_context]
    );
  }

  /**
   * Process entity.
   *
   * @param \Drupal\cloud\Entity\CloudContentEntityBase $entity
   *   An entity object.
   */
  protected function processEntity(CloudContentEntityBase $entity) {

    $entity->delete();
  }

  /**
   * Process an entity and related AWS resource.
   *
   * @param \Drupal\cloud\Entity\CloudContentEntityBase $entity
   *   An entity object.
   *
   * @return bool
   *   Succeeded or failed.
   */
  protected function process(CloudContentEntityBase $entity) {

    try {
      if ($this->processCloudResource($entity)) {

        $this->processEntity($entity);

        // Since we don't use EntityDeleteFormTrait since it is an abstract
        // class and requires to implement a getEntity() method, so we use our
        // own method here instead.
        $this->processOperationStatus($entity, 'deleted');

        return TRUE;
      }

      // Ditto.
      $this->processOperationErrorStatus($entity, 'deleted');

      return FALSE;
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }
  }

  /**
   * Returns the message to show the user after an item was processed.
   *
   * @param int $count
   *   Count of processed translations.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The item processed message.
   */
  protected function getProcessedMessage($count) {

    return $this->formatPlural($count, 'Deleted @count item.', 'Deleted @count items.');
  }

  /**
   * Returns the message to show the user when an item has not been processed.
   *
   * @param int $count
   *   Count of processed translations.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The item inaccessible message.
   */
  protected function getInaccessibleMessage($count) {

    return $this->formatPlural($count,
      '@count item has not been deleted because you do not have the necessary permissions.',
      '@count items have not been deleted because you do not have the necessary permissions.'
    );
  }

}
