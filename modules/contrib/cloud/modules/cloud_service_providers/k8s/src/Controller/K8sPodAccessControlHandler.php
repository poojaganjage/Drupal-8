<?php

namespace Drupal\k8s\Controller;

use Drupal\cloud\Traits\AccessCheckTrait;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Pod entity.
 *
 * @see \Drupal\k8s\Entity\K8sPod.
 */
class K8sPodAccessControlHandler extends EntityAccessControlHandler {

  use AccessCheckTrait;

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    $view_namespace_perm = 'view k8s namespace ' . $entity->getNamespace();
    switch ($operation) {
      case 'view':
      case 'log':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          [$view_namespace_perm, 'view own k8s pod'],
          [$view_namespace_perm, 'view any k8s pod']
        );

      case 'update':
      case 'edit':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          [$view_namespace_perm, 'edit own k8s pod'],
          [$view_namespace_perm, 'edit any k8s pod']
        );

      case 'delete':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          [$view_namespace_perm, 'delete own k8s pod'],
          [$view_namespace_perm, 'delete any k8s pod']
        );
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return $this->allowedIfCanAccessCloudConfig(
      NULL,
      $account,
      'add k8s pod'
    );
  }

}
