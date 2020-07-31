<?php

namespace Drupal\aws_cloud\Controller\Ec2;

use Drupal\cloud\Traits\AccessCheckTrait;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Snapshot entity.
 *
 * @see \Drupal\aws_cloud\Entity\Ec2\Snapshot.
 */
class SnapshotAccessControlHandler extends EntityAccessControlHandler {

  use AccessCheckTrait;
  use CloudContentEntityTrait;

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    // Get cloud service provider name.
    $cloud_name = $this->getModuleNameWhitespace($entity);

    switch ($operation) {
      case 'view':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          "view own {$cloud_name} snapshot",
          "view any {$cloud_name} snapshot"
        );

      case 'update':
      case 'edit':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          "edit own {$cloud_name} snapshot",
          "edit any {$cloud_name} snapshot"
        );

      case 'delete':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          "delete own {$cloud_name} snapshot",
          "delete any {$cloud_name} snapshot"
        );
    }
    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

}
