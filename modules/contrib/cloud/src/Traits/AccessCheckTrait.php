<?php

namespace Drupal\cloud\Traits;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * The trait checking permission.
 */
trait AccessCheckTrait {

  /**
   * Checks if a user can access the cloud service provider as an owner.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   * @param mixed $owner_permissions
   *   The permissions for the user who is owner.
   * @param mixed $any_permissions
   *   The permissions for the user who is not owner.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Allowed or forbidden, neutral if tempstore is empty.
   */
  protected function allowedIfCanAccessCloudConfigWithOwner(
    EntityInterface $entity,
    AccountInterface $account,
    $owner_permissions,
    $any_permissions
  ) {
    if (!$account->hasPermission('view all cloud service providers')
      && !$account->hasPermission('view ' . $entity->getCloudContext())) {
      return AccessResult::neutral();
    }

    if (!is_array($owner_permissions)) {
      $owner_permissions = [$owner_permissions];
    }

    if (!is_array($any_permissions)) {
      $any_permissions = [$any_permissions];
    }

    // If the account has any permissions.
    $result = AccessResult::allowedIfHasPermissions($account, $any_permissions);
    if ($result->isAllowed()) {
      return $result;
    }

    if ($entity->getOwner() === NULL) {
      return AccessResult::neutral();
    }

    if ($account->id() === $entity->getOwner()->id()) {
      return AccessResult::allowedIfHasPermissions($account, $owner_permissions);
    }
    else {
      return AccessResult::neutral();
    }

  }

  /**
   * Checks if a user can access the cloud service provider (CloudConfig).
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   * @param mixed $permissions
   *   The permissions for the user.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Allowed or forbidden, neutral if tempstore is empty.
   */
  protected function allowedIfCanAccessCloudConfig(
    EntityInterface $entity = NULL,
    AccountInterface $account,
    $permissions
  ) {
    if (!is_array($permissions)) {
      $permissions = [$permissions];
    }
    if ($entity === NULL) {
      $route = \Drupal::routeMatch();
      $cloud_context = $route->getParameter('cloud_context');
    }
    else {
      $cloud_context = $entity->getCloudContext();
    }

    if (!$account->hasPermission('view all cloud service providers')
      && !$account->hasPermission('view ' . $cloud_context)) {
      return AccessResult::neutral();
    }

    return AccessResult::allowedIfHasPermissions($account, $permissions);
  }

}
