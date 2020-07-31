<?php

namespace Drupal\k8s\Controller;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\cloud\Traits\AccessCheckTrait;

/**
 * Access controller for the Persistent Volume Claim entity.
 *
 * @see \Drupal\k8s\Entity\K8sPersistentVolumeClaim.
 */
class K8sPersistentVolumeClaimAccessControlHandler extends EntityAccessControlHandler {

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
          'view k8s persistent volume claim'
        );

      case 'update':
      case 'edit':
        return $this->allowedIfCanAccessCloudConfig(
          $entity,
          $account,
          'edit k8s persistent volume claim'
        );

      case 'delete':
        return $this->allowedIfCanAccessCloudConfig(
          $entity,
          $account,
          'delete k8s persistent volume claim'
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
      'add k8s persistent volume claim'
    );
  }

}
