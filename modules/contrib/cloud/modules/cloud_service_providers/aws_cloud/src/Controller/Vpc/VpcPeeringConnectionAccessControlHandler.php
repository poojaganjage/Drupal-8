<?php

namespace Drupal\aws_cloud\Controller\Vpc;

use Drupal\cloud\Traits\AccessCheckTrait;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the VPC Peering Connection entity.
 *
 * @see \Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnection.
 */
class VpcPeeringConnectionAccessControlHandler extends EntityAccessControlHandler {

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
          'view own aws cloud vpc peering connection',
          'view any aws cloud vpc peering connection'
        );

      case 'update':
      case 'edit':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          'edit own aws cloud vpc peering connection',
          'edit any aws cloud vpc peering connection'
        );

      case 'delete':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          'delete own aws cloud vpc peering connection',
          'delete any aws cloud vpc peering connection'
        );

      case 'accept':
        if ($entity->getStatusCode() === 'pending-acceptance') {
          return $this->allowedIfCanAccessCloudConfigWithOwner(
            $entity,
            $account,
            'edit own aws cloud vpc peering connection',
            'edit any aws cloud vpc peering connection'
          );
        }
        break;
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

}
