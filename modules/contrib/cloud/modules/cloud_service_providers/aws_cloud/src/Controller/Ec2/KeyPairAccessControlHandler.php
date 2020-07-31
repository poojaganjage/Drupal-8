<?php

namespace Drupal\aws_cloud\Controller\Ec2;

use Drupal\cloud\Traits\AccessCheckTrait;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the KeyPair entity.
 *
 * @see \Drupal\aws_cloud\Entity\Ec2\KeyPair\Entity\KeyPair.
 */
class KeyPairAccessControlHandler extends EntityAccessControlHandler {

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
          "view own {$cloud_name} key pair",
          "view any {$cloud_name} key pair"
        );

      case 'update':
      case 'edit':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          "edit own {$cloud_name} key pair",
          "edit any {$cloud_name} key pair"
        );

      case 'delete':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          "delete own {$cloud_name} key pair",
          "delete any {$cloud_name} key pair"
        );
    }
    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

}
