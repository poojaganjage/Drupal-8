<?php

namespace Drupal\aws_cloud\Controller\Vpc;

use Drupal\cloud\Traits\AccessCheckTrait;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the VPC entity.
 *
 * @see \Drupal\aws_cloud\Entity\Vpc\Vpc.
 */
class VpcAccessControlHandler extends EntityAccessControlHandler {

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
          'view own aws cloud vpc',
          'view any aws cloud vpc'
        );

      case 'update':
      case 'edit':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          'edit own aws cloud vpc',
          'edit any aws cloud vpc'
        );

      case 'delete':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          'delete own aws cloud vpc',
          'delete any aws cloud vpc'
        );

    }
    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

}
