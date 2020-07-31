<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface;
use Drupal\cloud\Form\CloudProcessMultipleForm;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an entities deletion confirmation form.
 */
abstract class AwsCloudProcessMultipleForm extends CloudProcessMultipleForm {

  /**
   * The AWS Cloud EC2 Service.
   *
   * @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface
   */
  protected $ec2Service;

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
      $cloud_config_plugin_manager
    );

    $this->ec2Service = $ec2_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('tempstore.private'),
      $container->get('messenger'),
      $container->get('plugin.manager.cloud_config_plugin'),
      $container->get('aws_cloud.ec2')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId() {

    return 'aws_cloud_process_multiple_confirm_form';
  }

}
