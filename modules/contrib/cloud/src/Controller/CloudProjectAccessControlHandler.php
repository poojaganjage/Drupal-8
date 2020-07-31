<?php

namespace Drupal\cloud\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the cloud project entity.
 *
 * @see \Drupal\cloud\Entity\CloudProject.
 */
class CloudProjectAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    // First check for cloud_context access.
    if (!$account->hasPermission('view all cloud service providers')
      && !$account->hasPermission('view ' . $entity->getCloudContext())
    ) {
      return AccessResult::neutral();
    }

    // Determine if the user is the entity owner ID.
    $is_entity_owner = $account->id() === $entity->getOwner()->id();

    /** @var \Drupal\cloud\Entity\CloudProjectInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          if ($account->hasPermission('view any unpublished cloud projects')) {
            return AccessResult::allowed();
          }
          return AccessResult::allowedIf($account->hasPermission('view own unpublished cloud projects') && $is_entity_owner);
        }

        if ($account->hasPermission('view any published cloud projects')) {
          return AccessResult::allowed();
        }
        return AccessResult::allowedIf($account->hasPermission('view own published cloud projects') && $is_entity_owner);

      case 'update':
        if ($account->hasPermission('edit any cloud projects')) {
          return AccessResult::allowed();
        }
        return AccessResult::allowedIf($account->hasPermission('edit own cloud projects') && $is_entity_owner);

      case 'delete':
        if ($account->hasPermission('delete any cloud projects')) {
          return AccessResult::allowed();
        }
        return AccessResult::allowedIf($account->hasPermission('delete own cloud projects') && $is_entity_owner);
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

}
