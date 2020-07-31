<?php

namespace Drupal\k8s\Controller;

use Drupal\cloud\Traits\AccessCheckTrait;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the ConfigMap entity.
 *
 * @see \Drupal\k8s\Entity\K8sConfigMap.
 */
class K8sConfigMapAccessControlHandler extends EntityAccessControlHandler {

  use AccessCheckTrait;

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    $view_namespace_perm = 'view k8s namespace ' . $entity->getNamespace();
    switch ($operation) {
      case 'view':
        return $this->allowedIfCanAccessCloudConfig(
          $entity,
          $account,
          [$view_namespace_perm, 'view k8s configmap']
        );

      case 'update':
      case 'edit':
        return $this->allowedIfCanAccessCloudConfig(
          $entity,
          $account,
          [$view_namespace_perm, 'edit k8s configmap']
        );

      case 'delete':
        return $this->allowedIfCanAccessCloudConfig(
          $entity,
          $account,
          [$view_namespace_perm, 'delete k8s configmap']
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
      'add k8s configmap'
    );
  }

}
