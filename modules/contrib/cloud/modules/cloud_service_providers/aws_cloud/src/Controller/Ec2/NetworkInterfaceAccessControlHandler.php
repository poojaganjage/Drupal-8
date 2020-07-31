<?php

namespace Drupal\aws_cloud\Controller\Ec2;

use Drupal\cloud\Traits\AccessCheckTrait;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the NetworkInterface entity.
 *
 * @see \Drupal\aws_cloud\Entity\Ec2\NetworkInterface\Entity\NetworkInterface.
 */
class NetworkInterfaceAccessControlHandler extends EntityAccessControlHandler {

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
          "view own {$cloud_name} network interface",
          "view any {$cloud_name} network interface"
        );

      case 'update':
      case 'edit':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          "edit own {$cloud_name} network interface",
          "edit any {$cloud_name} network interface"
        );

      case 'delete':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          "delete own {$cloud_name} network interface",
          "delete any {$cloud_name} network interface"
        );
    }
    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

}
