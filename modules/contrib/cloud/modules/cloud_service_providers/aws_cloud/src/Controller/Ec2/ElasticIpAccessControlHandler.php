<?php

namespace Drupal\aws_cloud\Controller\Ec2;

use Drupal\cloud\Traits\AccessCheckTrait;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the ElasticIp entity.
 *
 * @see \Drupal\aws_cloud\Entity\Ec2\ElasticIp.
 */
class ElasticIpAccessControlHandler extends EntityAccessControlHandler {

  use AccessCheckTrait;
  use CloudContentEntityTrait;

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    $cloud_name = $this->getModuleNameWhitespace($entity);

    if ($cloud_name === 'aws cloud') {
      $permission_type = "aws cloud elastic ip";
    }
    else {
      $permission_type = "openstack floating ip";
    }

    switch ($operation) {
      case 'view':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          "view own {$permission_type}",
          "view any {$permission_type}"
        );

      case 'update':
      case 'edit':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          "edit own {$permission_type}",
          "edit any {$permission_type}"
        );

      case 'delete':
        if ($entity->getAssociationId() === NULL) {
          return $this->allowedIfCanAccessCloudConfigWithOwner(
            $entity,
            $account,
            "delete own {$permission_type}",
            "delete any {$permission_type}"
          );
        }
        break;

      case 'associate':
        if ($entity->getAssociationId() === NULL) {
          return $this->allowedIfCanAccessCloudConfigWithOwner(
            $entity,
            $account,
            "edit own {$permission_type}",
            "edit any {$permission_type}"
          );
        }
        break;

      case 'disassociate':
        if ($entity->getAssociationId() !== NULL) {
          return $this->allowedIfCanAccessCloudConfigWithOwner(
            $entity,
            $account,
            "edit own {$permission_type}",
            "edit any {$permission_type}"
          );
        }
        break;
    }
    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

}
