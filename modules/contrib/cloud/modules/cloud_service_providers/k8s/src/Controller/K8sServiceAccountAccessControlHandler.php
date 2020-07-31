<?php

namespace Drupal\k8s\Controller;

use Drupal\cloud\Traits\AccessCheckTrait;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Service Account entity.
 *
 * @see \Drupal\k8s\Entity\K8sServiceAccount.
 */
class K8sServiceAccountAccessControlHandler extends EntityAccessControlHandler {

  use AccessCheckTrait;

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return $this->allowedIfCanAccessCloudConfig(
          $entity,
          $account,
          'view k8s service account'
        );

      case 'update':
      case 'edit':
        return $this->allowedIfCanAccessCloudConfig(
          $entity,
          $account,
          'edit k8s service account'
        );

      case 'delete':
        return $this->allowedIfCanAccessCloudConfig(
          $entity,
          $account,
          'delete k8s service account'
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
      'add k8s service account'
    );
  }

}
