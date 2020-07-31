<?php

namespace Drupal\cloud\Traits;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The cloud content entity trait.
 *
 * Handles various functions such as database operations (e.g. clear entities),
 * string manipulations, and standard status message constructions.
 */
trait CloudContentEntityTrait {

  use LoggerChannelTrait;
  use MessengerTrait;
  use StringTranslationTrait;

  /**
   * Helper method to clear cache values.
   */
  protected function clearCacheValues(): void {
    $this->pluginCacheClearer->clearCachedDefinitions();
    $this->cacheRender->invalidateAll();
  }

  /**
   * Clear entities.
   *
   * @param string $entity_type
   *   Entity Type.
   * @param int $timestamp
   *   The timestamp for condition of refreshed time to clear entities.
   */
  protected function clearEntities($entity_type, $timestamp) {
    $entity_ids = $this->entityTypeManager->getStorage($entity_type)->getQuery()
      ->condition('refreshed', $timestamp, '<')
      ->condition('cloud_context', $this->cloudContext)
      ->execute();
    if (count($entity_ids)) {
      $this->deleteEntities($entity_type, $entity_ids);
    }
  }

  /**
   * Helper method to delete entities.
   *
   * @param string $entity_type
   *   Entity Type.
   * @param array $entity_ids
   *   Array of entity IDs.
   */
  protected function deleteEntities($entity_type, array $entity_ids) {
    $entities = $this->entityTypeManager->getStorage($entity_type)->loadMultiple($entity_ids);
    $this->entityTypeManager->getStorage($entity_type)->delete($entities);
  }

  /**
   * Helper method to load all entities of a given type.
   *
   * @param string $entity_type
   *   Entity type.
   * @param array $conditions
   *   Query conditions.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Array of entities.
   */
  protected function loadAllEntities($entity_type, array $conditions = []) {
    $conditions['cloud_context'] = $this->cloudContext;
    return $this->entityTypeManager
      ->getStorage($entity_type)
      ->loadByProperties($conditions);
  }

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
    $provider = $this->getProviderWithUnderscore($entity->getEntityType()->getProvider());
    return substr($entity_type_id, strlen($provider));
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
    $provider = $this->getProviderWithUnderscore($entity->getEntityType()->getProvider());
    $short_name = substr($entity_type_id, strlen($provider));
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
    $provider = $this->getProviderWithUnderscore($entity->getEntityType()->getProvider());
    $short_name = '';
    if (!empty($entity_type_id_plural)) {
      $short_name = substr($entity_type_id_plural, strlen($provider));
    }
    return str_replace(' ', '', ucwords(str_replace('_', ' ', $short_name)));
  }

  /**
   * Take an entity_type and get the plural form.
   *
   * @param string $entity_type
   *   Entity type.
   *
   * @return array
   *   Array with singular and plural labels.
   */
  protected function getDisplayLabels($entity_type) {
    $labels = [];
    try {
      $type = \Drupal::entityTypeManager()
        ->getStorage($entity_type)
        ->getEntityType();
      $labels['plural'] = $type->getPluralLabel();
      $labels['singular'] = $type->getSingularLabel();
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }
    return $labels;
  }

  /**
   * Get a module name of the entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return string
   *   The module name. e.g. 'aws_cloud'.
   */
  protected function getModuleName(EntityInterface $entity): string {
    return $entity->getEntityType()->getProvider();
  }

  /**
   * Get a module name of the entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return string
   *   The entity's cloud name. e.g. 'aws_cloud' -> returns 'aws cloud'.
   */
  protected function getModuleNameWhitespace(EntityInterface $entity): string {
    return str_replace('_', ' ', $this->getModuleName($entity));
  }

  /**
   * Add an underscore to the provider string.
   *
   * @param string $provider
   *   The provider (ie. module name).
   *
   * @return string
   *   Provider with underscore.
   */
  private function getProviderWithUnderscore($provider) {
    return $provider . '_';
  }

  /**
   * Add 'created' | 'updated' status message and log its notice.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to handle.
   * @param string $passive_operation
   *   The passive voice e.g. 'created' | 'updated' | 'deleted'.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The status message for the operation.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function getOperationMessage(EntityInterface $entity, $passive_operation): TranslatableMarkup {

    if (empty($entity)) {
      return $this->t('The entity is empty.');
    }

    $entity_type = $entity->getEntityType();
    if (empty($entity_type)) {
      return $this->t('The entity type is empty.');
    }

    if (empty($passive_operation)) {
      return $this->t('The operation is empty.');
    }

    if ($passive_operation === 'deleted') {
      return $this->t('The @type %label has been @passive_operation.', [
        '@type' => $entity_type->getSingularLabel(),
        '%label' => $entity->label(),
        '@passive_operation' => $passive_operation,
      ]);
    }

    $label = $entity->label();
    if ($entity->hasLinkTemplate('canonical')) {
      $label = $entity->toLink($entity->label())->toString();
    }
    elseif ($entity->hasLinkTemplate('edit-form')) {
      $label = $entity->toLink($entity->label(), 'edit-form')->toString();
    }

    return $this->t('The @type %label has been @passive_operation.', [
      '@type' => $entity_type->getSingularLabel(),
      '%label' => $label,
      '@passive_operation' => $passive_operation,
    ]);
  }

  /**
   * Add a status error message and log its notice.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to handle.
   * @param string $passive_operation
   *   The passive voice e.g. 'created' | 'updated' | 'deleted'.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The status message for the operation.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function getOperationErrorMessage(EntityInterface $entity, $passive_operation): TranslatableMarkup {

    if (empty($entity)) {
      return $this->t('The entity is empty.');
    }

    $entity_type = $entity->getEntityType();
    if (empty($entity_type)) {
      return $this->t('The entity type is empty.');
    }

    if (empty($passive_operation)) {
      return $this->t('The operation is empty.');
    }

    if ($passive_operation === 'created') {
      return $this->t('The @type @label could not be @passive_operation.', [
        '@type' => $entity_type->getSingularLabel(),
        '@label' => $entity->label(),
        '@passive_operation' => $passive_operation,
      ]);
    }

    $label = $entity->label();
    if ($entity->hasLinkTemplate('canonical')) {
      $label = $entity->toLink($entity->label())->toString();
    }
    elseif ($entity->hasLinkTemplate('edit-form')) {
      $label = $entity->toLink($entity->label(), 'edit-form')->toString();
    }

    return $this->t('The @type %label could not be @passive_operation.', [
      '@type' => $entity_type->getSingularLabel(),
      '%label' => $label,
      '@passive_operation' => $passive_operation,
    ]);
  }

  /**
   * Add a status message and log its notice.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to handle.
   * @param string $passive_operation
   *   The passive voice e.g. 'created' | 'updated' | 'deleted'.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function logOperationMessage(EntityInterface $entity, $passive_operation): void {

    $channel = $this->getModuleName($entity);

    if (empty($channel)) {
      $this->logger('cloud')->error($this->t('The module name is empty.'));
      return;
    }

    if (empty($entity)) {
      $this->logger('cloud')->error($this->t('The entity is empty.'));
      return;
    }

    $entity_type = $entity->getEntityType();
    if (empty($entity_type)) {
      $this->logger('cloud')->error($this->t('The entity type is empty.'));
      return;
    }

    if (empty($passive_operation)) {
      $this->logger('cloud')->error($this->t('The operation is empty.'));
      return;
    }

    if ($passive_operation === 'deleted') {

      // @label doesn't have any link since it is already deleted.
      $this->logger($channel)->notice('@type: @passive_operation @label.', [
        '@type' => $entity_type->getLabel(),
        '@passive_operation' => $passive_operation,
        '@label' => $entity->label(),
      ]);

      // Skip the following code if $passive_operation is 'deleted'.
      return;
    }

    $link = [];
    if ($entity->hasLinkTemplate('canonical')) {
      $link = $entity->toLink($this->t('View'))->toString();
    }
    elseif ($entity->hasLinkTemplate('edit-form')) {
      $link = $entity->toLink('View', 'edit-form')->toString();
    }

    $this->logger($channel)->notice('@type: @passive_operation %label.', [
      '@type' => $entity_type->getLabel(),
      '@passive_operation' => $passive_operation,
      '%label' => $entity->label(),
      'link' => $link,
    ]);
  }

  /**
   * Add an error message and log its error.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to handle.
   * @param string $passive_operation
   *   The passive voice e.g. 'created' | 'updated' | 'deleted'.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function logOperationErrorMessage(EntityInterface $entity, $passive_operation): void {

    $channel = $this->getModuleName($entity);

    if (empty($channel)) {
      $this->logger('cloud')->error($this->t('The module name is empty.'));
      return;
    }

    if (empty($entity)) {
      $this->logger('cloud')->error($this->t('The entity is empty.'));
      return;
    }

    $entity_type = $entity->getEntityType();
    if (empty($entity_type)) {
      $this->logger('cloud')->error($this->t('The entity type is empty.'));
      return;
    }

    if (empty($passive_operation)) {
      $this->logger('cloud')->error($this->t('The operation is empty.'));
      return;
    }

    if ($passive_operation === 'created') {

      $this->logger($channel)->error($this->t('@type: @label could not be @passive_operation.', [
        '@type' => $entity_type->getLabel(),
        '@label' => $entity->label(),
        '@passive_operation' => $passive_operation,
      ]));

      // Skip the following code if $present_operation is 'create'.
      return;
    }

    $link = [];
    if ($entity->hasLinkTemplate('canonical')) {
      $link = $entity->toLink($this->t('View'))->toString();
    }
    elseif ($entity->hasLinkTemplate('edit-form')) {
      $link = $entity->toLink('View', 'edit-form')->toString();
    }

    $this->logger($channel)->error($this->t('@type: %label could not be @passive_operation.', [
      '@type' => $entity_type->getLabel(),
      '%label' => $entity->label(),
      '@passive_operation' => $passive_operation,
      'link' => $link,
    ]));
  }

  /**
   * Add a status message and log its notice.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to handle.
   * @param string $passive_operation
   *   The passive voice e.g. 'created' | 'updated' | 'deleted'.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function processOperationStatus(EntityInterface $entity, $passive_operation): void {

    // Using MessengerTrait::messenger since $this->messenger might not be
    // available at the caller of this method.
    $this->messenger()->addStatus($this->getOperationMessage($entity, $passive_operation));
    $this->logOperationMessage($entity, $passive_operation);
  }

  /**
   * Add a status error message and log its error.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to handle.
   * @param string $passive_operation
   *   The passive voice e.g. 'create' | 'update' | 'delete'.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function processOperationErrorStatus(EntityInterface $entity, $passive_operation): void {

    // Using MessengerTrait::messenger since $this->messenger might not be
    // available at the caller of this method.
    $this->messenger()->addError($this->getOperationErrorMessage($entity, $passive_operation));
    $this->logOperationErrorMessage($entity, $passive_operation);
  }

  /**
   * Add a status error message for an exception.
   *
   * @param \Exception $e
   *   An exception object.
   */
  public function handleException(\Exception $e): void {

    // Using MessengerTrait::messenger since $this->messenger might not be
    // available at the caller of this method.
    $this->messenger()->addError($this->t('An error occurred: @exception', [
      '@exception' => $e->getMessage(),
    ]));
  }

  /**
   * Gets the logger for a specific channel.
   *
   * This method exists for backward-compatibility between FormBase and
   * LoggerChannelTrait. Use LoggerChannelTrait::getLogger() instead.
   *
   * @param string $channel
   *   The name of the channel. Can be any string, but the general practice is
   *   to use the name of the subsystem calling this.
   *
   * @return \Psr\Log\LoggerInterface
   *   The logger for the given channel.
   */
  public function logger($channel) {
    return $this->getLogger($channel);
  }

  /**
   * Helper method to load an entity using parameters.
   *
   * @param string $entity_type
   *   Entity Type.
   * @param string $id_field
   *   Entity ID field.
   * @param string $id_value
   *   Entity ID value.
   * @param array $extra_conditions
   *   Extra conditions.
   *
   * @return int
   *   Entity ID.
   */
  public function getEntityId($entity_type, $id_field, $id_value, array $extra_conditions = []) {
    $query = $this->entityTypeManager
      ->getStorage($entity_type)
      ->getQuery()
      ->condition($id_field, $id_value)
      ->condition('cloud_context', $this->cloudContext);

    foreach ($extra_conditions ?: [] as $key => $value) {
      $query->condition($key, $value);
    }

    $entities = $query->execute();
    return array_shift($entities);
  }

}
