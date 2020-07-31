<?php

namespace Drupal\terraform\Form;

use Drupal\cloud\Form\CloudProcessMultipleForm;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\terraform\Service\TerraformServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an entities deletion confirmation form.
 */
abstract class TerraformProcessMultipleForm extends CloudProcessMultipleForm {

  /**
   * The Terraform Service.
   *
   * @var \Drupal\terraform\Service\TerraformServiceInterface
   */
  protected $terraformService;

  /**
   * Constructs a new TerraformProcessMultipleForm object.
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
   * @param \Drupal\terraform\Service\TerraformServiceInterface $terraform_service
   *   The Terraform Service.
   */
  public function __construct(AccountInterface $current_user,
                              EntityTypeManagerInterface $entity_type_manager,
                              PrivateTempStoreFactory $temp_store_factory,
                              MessengerInterface $messenger,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
                              TerraformServiceInterface $terraform_service) {

    parent::__construct(
      $current_user,
      $entity_type_manager,
      $temp_store_factory,
      $messenger,
      $cloud_config_plugin_manager
    );

    $this->terraformService = $terraform_service;
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
      $container->get('terraform')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId() {

    return 'terraform_process_multiple_confirm_form';
  }

}
