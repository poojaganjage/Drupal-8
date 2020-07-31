<?php

namespace Drupal\aws_cloud\Controller\Ec2;

use Drupal\cloud\Traits\AccessCheckTrait;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Instance entity.
 *
 * @see \Drupal\aws_cloud\Entity\Ec2\Instance.
 */
class InstanceAccessControlHandler extends EntityAccessControlHandler {

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
          "view own {$cloud_name} instance",
          "view any {$cloud_name} instance"
        );

      case 'update':
      case 'edit':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          "edit own {$cloud_name} instance",
          "edit any {$cloud_name} instance"
        );

      case 'delete':
        return $this->allowedIfCanAccessCloudConfigWithOwner(
          $entity,
          $account,
          "delete own {$cloud_name} instance",
          "delete any {$cloud_name} instance"
        );

      case 'start':
        if ($entity->getInstanceState() === 'stopped') {
          return $this->allowedIfCanAccessCloudConfigWithOwner(
            $entity,
            $account,
            "edit own {$cloud_name} instance",
            "edit any {$cloud_name} instance"
          );
        }
        break;

      case 'stop':
        if ($entity->getInstanceState() === 'running') {
          return $this->allowedIfCanAccessCloudConfigWithOwner(
            $entity,
            $account,
            "edit own {$cloud_name} instance",
            "edit any {$cloud_name} instance"
          );
        }
        break;

      case 'reboot':
        if ($entity->getInstanceState() === 'running') {
          return $this->allowedIfCanAccessCloudConfigWithOwner(
            $entity,
            $account,
            "edit own {$cloud_name} instance",
            "edit any {$cloud_name} instance"
          );
        }
        break;

      case 'associate_elastic_ip':
        if ($entity->getInstanceState() === 'stopped' && aws_cloud_can_attach_ip($entity) === TRUE && count(aws_cloud_get_available_elastic_ips($entity->getCloudContext()))) {
          return $this->allowedIfCanAccessCloudConfigWithOwner(
            $entity,
            $account,
            "edit own {$cloud_name} instance",
            "edit any {$cloud_name} instance"
          );
        }
        break;
    }
    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

}
