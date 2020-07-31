<?php

namespace Drupal\vmware\Controller;

use Drupal\cloud\Traits\AccessCheckTrait;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the VmwareVm entity.
 *
 * @see \Drupal\vmware\Entity\VmwareVm.
 */
class VmwareVmAccessControlHandler extends EntityAccessControlHandler {

  use AccessCheckTrait;

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'start':
        if ($entity->getPowerState() === 'POWERED_OFF') {
          return $this->allowedIfCanAccessCloudConfig(
            $entity,
            $account,
            'edit vmware vm'
          );
        }
        break;

      case 'stop':
        if ($entity->getPowerState() === 'POWERED_ON') {
          return $this->allowedIfCanAccessCloudConfig(
            $entity,
            $account,
            'edit vmware vm'
          );
        }
        break;

      case 'view':
        return $this->allowedIfCanAccessCloudConfig(
          $entity,
          $account,
          'view vmware vm'
        );

      case 'update':
      case 'edit':
        return $this->allowedIfCanAccessCloudConfig(
          $entity,
          $account,
          'edit vmware vm'
        );

      case 'delete':
        return $this->allowedIfCanAccessCloudConfig(
          $entity,
          $account,
          'delete vmware vm'
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
      'add vmware vm'
    );
  }

}
