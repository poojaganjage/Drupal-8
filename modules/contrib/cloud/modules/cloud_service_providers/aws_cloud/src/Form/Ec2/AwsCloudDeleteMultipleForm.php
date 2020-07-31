<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Traits\CloudDeleteMultipleFormTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;

/**
 * Provides an entities deletion confirmation form.
 */
abstract class AwsCloudDeleteMultipleForm extends AwsCloudProcessMultipleForm {

  use CloudDeleteMultipleFormTrait;

  /**
   * Constructs a new AwsCloudProcessMultiple object.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   * @param \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface $ec2_service
   *   The AWS Cloud EC2 Service.
   */
  public function __construct(AccountInterface $current_user,
                              EntityTypeManagerInterface $entity_type_manager,
                              PrivateTempStoreFactory $temp_store_factory,
                              MessengerInterface $messenger,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
                              Ec2ServiceInterface $ec2_service) {

    parent::__construct(
      $current_user,
      $entity_type_manager,
      $temp_store_factory,
      $messenger,
      $cloud_config_plugin_manager,
      $ec2_service
    );
    $this->tempStore = $temp_store_factory->get('entity_delete_multiple_confirm');
  }

}
