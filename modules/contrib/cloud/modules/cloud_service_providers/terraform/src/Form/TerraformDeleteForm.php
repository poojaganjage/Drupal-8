<?php

namespace Drupal\terraform\Form;

use Drupal\cloud\Form\CloudContentDeleteForm;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Service\EntityLinkRendererInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Plugin\CachedDiscoveryClearerInterface;
use Drupal\terraform\Service\TerraformServiceException;
use Drupal\terraform\Service\TerraformServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TerraformDeleteForm - Base Delete class.
 *
 * This class injects the TerraformServiceInterface and Messenger for use.
 *
 * @package Drupal\terraform\Form
 */
class TerraformDeleteForm extends CloudContentDeleteForm {

  /**
   * The Terraform Service.
   *
   * @var \Drupal\terraform\Service\TerraformServiceInterface
   */
  protected $terraformService;

  /**
   * The Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * A plugin cache clear instance.
   *
   * @var \Drupal\Core\Plugin\CachedDiscoveryClearerInterface
   */
  protected $pluginCacheClearer;

  /**
   * A cache backend interface instance.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheRender;

  /**
   * Entity link renderer object.
   *
   * @var \Drupal\cloud\Service\EntityLinkRendererInterface
   */
  protected $entityLinkRenderer;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * TerraformDeleteForm constructor.
   *
   * @param \Drupal\terraform\Service\TerraformServiceInterface $terraform_service
   *   The Terraform Service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The messenger service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The Entity Type Manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheRender
   *   A cache backend interface instance.
   * @param \Drupal\Core\Plugin\CachedDiscoveryClearerInterface $plugin_cache_clearer
   *   A plugin cache clear instance.
   * @param \Drupal\cloud\Service\EntityLinkRendererInterface $entity_link_renderer
   *   The entity link render service.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   */
  public function __construct(TerraformServiceInterface $terraform_service,
                              EntityRepositoryInterface $entity_repository,
                              EntityTypeBundleInfoInterface $entity_type_bundle_info,
                              TimeInterface $time,
                              Messenger $messenger,
                              EntityTypeManager $entity_type_manager,
                              CacheBackendInterface $cacheRender,
                              CachedDiscoveryClearerInterface $plugin_cache_clearer,
                              EntityLinkRendererInterface $entity_link_renderer,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time, $messenger);
    $this->terraformService = $terraform_service;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityLinkRenderer = $entity_link_renderer;
    $this->cacheRender = $cacheRender;
    $this->pluginCacheClearer = $plugin_cache_clearer;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('terraform'),
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('messenger'),
      $container->get('entity_type.manager'),
      $container->get('cache.render'),
      $container->get('plugin.cache_clearer'),
      $container->get('entity.link_renderer'),
      $container->get('plugin.manager.cloud_config_plugin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $name_underscore = $this->getShortEntityTypeNameUnderscore($entity);
    $name_camel = $this->getShortEntityTypeNameCamel($this->entity);

    $this->terraformService->setCloudContext($entity->getCloudContext());
    try {
      $method_name = "delete{$name_camel}";

      $this->terraformService->$method_name($entity->getName());

      $entity->delete();
      $this->messenger->addStatus($this->getDeletionMessage());
      $this->logDeletionMessage();
    }
    catch (TerraformServiceException $e) {

      $this->processOperationErrorStatus($entity, 'deleted');
    }

    $params = ['cloud_context' => $entity->getCloudContext()];
    if ($name_underscore !== 'workspace') {
      $params['terraform_workspace'] = $entity->getTerraformWorkspaceId();
    }
    $form_state->setRedirect("view.terraform_{$name_underscore}.list", $params);
    $this->clearCacheValues();
  }

}
