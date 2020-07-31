<?php

namespace Drupal\cloud\Controller;

use Drupal\cloud\Traits\AccessCheckTrait;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the cloud service provider (CloudConfig) entity.
 *
 * @see \Drupal\cloud\Entity\CloudConfig.
 */
class CloudConfigAccessControlHandler extends EntityAccessControlHandler {

  use AccessCheckTrait;

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\cloud\Entity\CloudConfigInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return $this->allowedIfCanAccessCloudConfigWithOwner(
            $entity,
            $account,
            'view own unpublished cloud service providers',
            'view unpublished cloud service providers'
          );
        }

        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          'view own published cloud service providers',
          'view published cloud service providers'
        );

      case 'update':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          'edit own cloud service providers',
          'edit cloud service providers'
        );

      case 'delete':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          'delete own cloud service providers',
          'delete cloud service providers'
        );
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

}
