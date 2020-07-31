<?php

namespace Drupal\aws_cloud\Controller\Ec2;

use Drupal\cloud\Traits\AccessCheckTrait;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Volume entity.
 *
 * @see \Drupal\aws_cloud\Entity\Ec2\Volume.
 */
class VolumeAccessControlHandler extends EntityAccessControlHandler {

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
          "view own {$cloud_name} volume",
          "view any {$cloud_name} volume"
        );

      case 'update':
      case 'edit':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          "edit own {$cloud_name} volume",
          "edit any {$cloud_name} volume"
        );

      case 'delete':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          "delete own {$cloud_name} volume",
          "delete any {$cloud_name} volume"
        );

      case 'attach':
        if ($entity->getState() === 'available') {
          return $this->allowedIfCanAccessCloudConfigWithOwner(
            $entity,
            $account,
            "edit own {$cloud_name} volume",
            "edit any {$cloud_name} volume"
          );
        }
        break;

      case 'detach':
        if ($entity->getState() === 'in-use') {
          return $this->allowedIfCanAccessCloudConfigWithOwner(
            $entity,
            $account,
            "edit own {$cloud_name} volume",
            "edit any {$cloud_name} volume"
          );
        }
        break;
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

}
