<?php

namespace Drupal\k8s\Plugin\Block;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginException;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Service\CloudServiceInterface;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Link;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\k8s\Service\CostFieldsRendererInterface;
use Drupal\k8s\Service\K8sServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;

/**
 * Base class for K8s blocks.
 *
 * Extend this class to access common the services.
 */
abstract class K8sBaseBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use CloudContentEntityTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The page cache kill switch service.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The cost fields renderer.
   *
   * @var \Drupal\k8s\Service\CostFieldsRendererInterface
   */
  protected $costFieldsRenderer;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Class Resolver service.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  protected $classResolver;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The K8s Service.
   *
   * @var \Drupal\cloud\Service\CloudServiceInterface
   */
  protected $cloudService;

  /**
   * The K8s Service.
   *
   * @var \Drupal\k8s\Service\K8sServiceInterface
   */
  protected $k8sService;

  /**
   * Request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Constructs a new K8sNodeHeatmapBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match service.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $kill_switch
   *   The page cache kill switch service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\k8s\Service\CostFieldsRendererInterface $cost_fields_renderer
   *   The cost fields renderer.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The Messenger Service.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   The class resolver service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\cloud\Service\CloudServiceInterface $cloud_service
   *   The Cloud service.
   * @param \Drupal\k8s\Service\K8sServiceInterface $k8s_service
   *   The K8s service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    RouteMatchInterface $route_match,
    KillSwitch $kill_switch,
    ModuleHandlerInterface $module_handler,
    CostFieldsRendererInterface $cost_fields_renderer,
    Messenger $messenger,
    CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
    ConfigFactoryInterface $config_factory,
    UrlGeneratorInterface $url_generator,
    ClassResolverInterface $class_resolver,
    AccountInterface $current_user,
    CloudServiceInterface $cloud_service,
    K8sServiceInterface $k8s_service,
    RequestStack $request_stack
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
    $this->killSwitch = $kill_switch;
    $this->moduleHandler = $module_handler;
    $this->costFieldsRenderer = $cost_fields_renderer;
    $this->messenger = $messenger;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
    $this->configFactory = $config_factory;
    $this->urlGenerator = $url_generator;
    $this->classResolver = $class_resolver;
    $this->currentUser = $current_user;
    $this->cloudService = $cloud_service;
    $this->k8sService = $k8s_service;
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_route_match'),
      $container->get('page_cache_kill_switch'),
      $container->get('module_handler'),
      $container->get('k8s.cost_fields_renderer'),
      $container->get('messenger'),
      $container->get('plugin.manager.cloud_config_plugin'),
      $container->get('config.factory'),
      $container->get('url_generator'),
      $container->get('class_resolver'),
      $container->get('current_user'),
      $container->get('cloud'),
      $container->get('k8s'),
      $container->get('request_stack')
    );
  }

  /**
   * Check if Metrics server is enabled.
   *
   * @param string $cloud_context
   *   Cloud context to check.  If empty, check all K8s Nodes.
   * @param string $message
   *   The message to show if no metrics servers are found.
   *
   * @return bool
   *   TRUE|FALSE whether metrics is enabled.
   */
  protected function isMetricsServerEnabled($cloud_context, $message) {
    $metrics_enabled = FALSE;
    if (!empty($cloud_context)) {
      $metrics_enabled = k8s_is_metrics_enabled($cloud_context);
    }
    else {
      try {
        // If no cloud_context passed, check all k8s cloud service providers.
        // If there is one match, return TRUE.
        $ids = $this->entityTypeManager->getStorage('k8s_node')
          ->getQuery()
          ->execute();

        $nodes = $this->entityTypeManager->getStorage('k8s_node')
          ->loadMultiple($ids);
        foreach ($nodes ?: [] as $node) {
          if (k8s_is_metrics_enabled($node->getCloudContext()) === TRUE) {
            $metrics_enabled = TRUE;
            break;
          }
        }
      }
      catch (\Exception $e) {
        $this->handleException($e);
      }
    }

    if ($metrics_enabled === FALSE) {
      $this->messenger->addWarning($this->t('@message Please install @metrics_server_link', [
        '@message' => $message,
        '@metrics_server_link' => $this->k8sService->getMetricsServerLink($cloud_context),
      ]));
    }
    return $metrics_enabled;
  }

  /**
   * Set a message that the cloud_context needs to be refreshed.
   *
   * @param string $cloud_context
   *   The cloud context used to build the message.
   * @param string $message
   *   Optional message to display to users.
   */
  protected function setUpdateMessage($cloud_context, $message) {
    try {
      $this->cloudConfigPluginManager->setCloudContext($cloud_context);
      $cloud_config = $this->cloudConfigPluginManager->loadConfigEntity();
      $update_link = Link::fromTextAndUrl(
        $this->t('Update Data'),
        Url::fromRoute(
          'k8s.update_all_resources',
          ['cloud_context' => $cloud_context]
        )
      )->toString();
      $cloud_config_link = Link::fromTextAndUrl(
        $cloud_config->getName(),
        Url::fromRoute(
        'entity.cloud_config.edit_form', [
          'cloud_config' => $cloud_config->id(),
        ]
      )
      )->toString();
      $this->messenger->addWarning($this->t('@message Please click @page_link or update @cloud_service_provider.', [
        '@message' => $message,
        '@cloud_service_provider' => $cloud_config_link,
        '@page_link' => $update_link,
      ]));
    }
    catch (CloudConfigPluginException $e) {
      $this->messenger->addError($this->t('Cloud service provider @cloud_context not available.  Please add @cloud_context as a @link', [
        '@cloud_context' => $cloud_context,
        '@link' => Link::fromTextAndUrl(
          $this->t('Cloud Service Provider'),
          Url::fromRoute(
           'entity.cloud_config.add_form', ['cloud_config_type' => 'k8s']
          )
        )->toString(),
      ]));
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }
  }

  /**
   * Check if there are k8s entities in the system.
   *
   * @return bool
   *   True|False if there are k8s entities in the system.
   */
  public function isK8sEmpty() {
    $entities = $this->cloudConfigPluginManager->loadConfigEntities('k8s');
    return empty($entities) ? TRUE : FALSE;
  }

}
