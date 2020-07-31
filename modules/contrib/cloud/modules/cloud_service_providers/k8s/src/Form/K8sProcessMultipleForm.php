<?php

namespace Drupal\k8s\Form;

use Drupal\cloud\Form\CloudProcessMultipleForm;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\k8s\Service\K8sServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an entities deletion confirmation form.
 */
abstract class K8sProcessMultipleForm extends CloudProcessMultipleForm {

  /**
   * The K8s Service.
   *
   * @var \Drupal\k8s\Service\K8sServiceInterface
   */
  protected $k8sService;

  /**
   * Constructs a new K8sProcessMultipleForm object.
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
   * @param \Drupal\k8s\Service\K8sServiceInterface $k8s_service
   *   The K8s Service.
   */
  public function __construct(AccountInterface $current_user,
                              EntityTypeManagerInterface $entity_type_manager,
                              PrivateTempStoreFactory $temp_store_factory,
                              MessengerInterface $messenger,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
                              K8sServiceInterface $k8s_service) {

    parent::__construct(
      $current_user,
      $entity_type_manager,
      $temp_store_factory,
      $messenger,
      $cloud_config_plugin_manager
    );

    $this->k8sService = $k8s_service;
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
      $container->get('k8s')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId() {

    return 'k8s_process_multiple_confirm_form';
  }

}
