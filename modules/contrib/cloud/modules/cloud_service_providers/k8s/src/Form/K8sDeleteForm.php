<?php

namespace Drupal\k8s\Form;

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
use Drupal\k8s\Service\K8sServiceException;
use Drupal\k8s\Service\K8sServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class K8sDeleteForm - Base Delete class.
 *
 * This class injects the K8sServiceInterface and Messenger for use.
 *
 * @package Drupal\k8s\Form
 */
class K8sDeleteForm extends CloudContentDeleteForm {

  /**
   * The K8s Service.
   *
   * @var \Drupal\k8s\Service\K8sServiceInterface
   */
  protected $k8sService;

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
   * K8sDeleteForm constructor.
   *
   * @param \Drupal\k8s\Service\K8sServiceInterface $k8s_service
   *   The K8s Service.
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
  public function __construct(K8sServiceInterface $k8s_service,
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
    $this->k8sService = $k8s_service;
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
      $container->get('k8s'),
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
   *
   * @throws \Drupal\k8s\Service\K8sServiceException
   *    Thrown when unable to delete entity.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $name_underscore = $this->getShortEntityTypeNameUnderscore($entity);
    $name_camel = $this->getShortEntityTypeNameCamel($this->entity);

    $this->k8sService->setCloudContext($entity->getCloudContext());
    try {
      $method_name = "delete{$name_camel}";

      if (method_exists($entity, 'getNamespace')) {
        $this->k8sService->$method_name($entity->getNamespace(), [
          'metadata' => [
            'name' => $entity->getName(),
          ],
        ]);
      }
      else {
        $this->k8sService->$method_name([
          'metadata' => [
            'name' => $entity->getName(),
          ],
        ]);
      }

      $entity->delete();

      $this->messenger->addStatus($this->getDeletionMessage());
      $this->logDeletionMessage();
    }
    catch (K8sServiceException $e) {

      $this->processOperationErrorStatus($entity, 'deleted');
    }

    $form_state->setRedirect("view.k8s_{$name_underscore}.list", ['cloud_context' => $entity->getCloudContext()]);
  }

}
