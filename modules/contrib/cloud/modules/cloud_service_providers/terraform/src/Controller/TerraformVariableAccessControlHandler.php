<?php

namespace Drupal\terraform\Controller;

use Drupal\cloud\Traits\AccessCheckTrait;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the TerraformVariableAccessControlHandler entity.
 *
 * @see \Drupal\terraform\Entity\TerraformVariable.
 */
class TerraformVariableAccessControlHandler extends EntityAccessControlHandler {

  use AccessCheckTrait;

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'apply':
      case 'view':
        return $this->allowedIfCanAccessCloudConfig(
          $entity,
          $account,
          'view terraform variable'
        );

      case 'update':
      case 'edit':
        return $this->allowedIfCanAccessCloudConfig(
          $entity,
          $account,
          'edit terraform variable'
        );

      case 'delete':
        return $this->allowedIfCanAccessCloudConfig(
          $entity,
          $account,
          'delete terraform variable'
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
      'add terraform variable'
    );
  }

}
