<?php

namespace Drupal\aws_cloud\Controller\Vpc;

use Drupal\cloud\Traits\AccessCheckTrait;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Subnet entity.
 *
 * @see \Drupal\aws_cloud\Entity\Vpc\Subnet.
 */
class SubnetAccessControlHandler extends EntityAccessControlHandler {

  use AccessCheckTrait;

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          'view own aws cloud subnet',
          'view any aws cloud subnet'
        );

      case 'update':
      case 'edit':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          'edit own aws cloud subnet',
          'edit any aws cloud subnet'
        );

      case 'delete':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          'delete own aws cloud subnet',
          'delete any aws cloud subnet'
        );
    }
    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

}
