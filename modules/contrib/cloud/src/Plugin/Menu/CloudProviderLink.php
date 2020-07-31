<?php

namespace Drupal\cloud\Plugin\Menu;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Menu\StaticMenuLinkOverridesInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class adjusts the cloud service provider title and link.
 */
class CloudProviderLink extends MenuLinkDefault {

  /**
   * {@inheritdoc}
   */
  protected $overrideAllowed = [
    'menu_name' => 'main',
    'expanded' => TRUE,
  ];

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Creates a LocalTasksBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Menu\StaticMenuLinkOverridesInterface $static_override
   *   The static override storage.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The local task manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   A logger instance.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    StaticMenuLinkOverridesInterface $static_override,
    CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
    RouteMatchInterface $route_match,
    LoggerChannelFactoryInterface $logger_factory,
    MessengerInterface $messenger
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $static_override);
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
    $this->routeMatch = $route_match;
    $this->logger = $logger_factory->get('CloudProviderLink');
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu_link.static.overrides'),
      $container->get('plugin.manager.cloud_config_plugin'),
      $container->get('current_route_match'),
      $container->get('logger.factory'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    $title = $this->t('Cloud service providers');
    $cloud_context = $this->routeMatch->getParameter('cloud_context');
    if ($cloud_context !== NULL) {
      try {
        $this->cloudConfigPluginManager->setCloudContext($cloud_context);
        $config = $this->cloudConfigPluginManager->loadConfigEntity();
        $title = $config->getName();
      }
      catch (\Exception $e) {
        $message = $this->t(
          'Cannot load cloud service provider plugin: %cloud_context (CloudConfig::$cloud_context)', [
            '%cloud_context' => $cloud_context,
          ]
        );
        $this->messenger->addError($message);
        $this->logger->error($message);
      }
    }
    return $title;
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteName() {
    $route = 'view.cloud_config.list';
    $cloud_context = $this->routeMatch->getParameter('cloud_context');
    if ($cloud_context !== NULL) {
      try {
        $this->cloudConfigPluginManager->setCloudContext($cloud_context);
        $route = $this->cloudConfigPluginManager->getInstanceCollectionTemplateName();
      }
      catch (\Exception $e) {
        $message = $this->t(
          'Cannot load cloud service provider plugin: %cloud_context (CloudConfig::$cloud_context)', [
            '%cloud_context' => $cloud_context,
          ]
        );
        $this->messenger->addError($message);
        $this->logger->error($message);
      }
    }
    return $route;
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteParameters() {
    $cloud_context = $this->routeMatch->getParameter('cloud_context');
    if ($cloud_context !== NULL) {
      return ['cloud_context' => $cloud_context];
    }
    return parent::getRouteParameters();
  }

}
