<?php

namespace Drupal\cloud\Plugin\cloud\config;

use Drupal\cloud\Plugin\cloud\CloudPluginManager;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the default cloud_config_plugin manager.
 */
class CloudConfigPluginManager extends CloudPluginManager implements CloudConfigPluginManagerInterface {

  /**
   * Provides default values for all cloud_config_plugin plugins.
   *
   * @var array
   */
  protected $defaults = [
    'id' => 'cloud_config',
    'entity_type' => 'cloud_config',
  ];

  /**
   * The cloud context.
   *
   * @var string
   */
  private $cloudContext;

  /**
   * The cloud service provider plugin (CloudConfigPlugin).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginInterface
   */
  private $plugin;

  /**
   * Constructs a new CloudConfigPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   */
  public function __construct(
    \Traversable $namespaces,
    ModuleHandlerInterface $module_handler,
    CacheBackendInterface $cache_backend
  ) {

    parent::__construct('Plugin\cloud\config', $namespaces, $module_handler);

    // Add more services as required.
    $this->moduleHandler = $module_handler;
    $this->setCacheBackend($cache_backend, 'cloud_config_plugin', ['cloud_config_plugin']);
  }

  /**
   * Dependency Injection.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Instance of ContainerInterface.
   *
   * @return CloudConfigPluginManager
   *   return created object.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('container.namespaces'),
      $container->get('module_handler'),
      $container->get('cache.discovery')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $this->discovery = new YamlDiscovery('cloud.config.plugin', $this->moduleHandler->getModuleDirectories());
      $this->discovery->addTranslatableProperty('label', 'label_context');
      $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    }
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);

    if (empty($definition['id'])) {
      throw new PluginException(sprintf('Example plugin property (%s) definition "is" is required.', $plugin_id));
    }

    if (empty($definition['entity_bundle'])) {
      throw new PluginException(sprintf('entity_bundle property is required for (%s)', $plugin_id));
    }

    if (!isset($definition['base_plugin']) && empty($definition['cloud_context'])) {
      throw new PluginException(sprintf('cloud_context property is required for (%s)', $plugin_id));
    }
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginException
   */
  public function setCloudContext($cloud_context) {
    $this->cloudContext = $cloud_context;
    // Load the plugin variant since we know the cloud_context.
    $this->plugin = $this->loadPluginVariant();
    if ($this->plugin === FALSE) {
      $message = $this->t(
        'Cannot load cloud service provider plugin: %cloud_context (CloudConfig::$cloudContext)', [
          '%cloud_context' => $this->cloudContext,
        ]
      );
      throw new CloudConfigPluginException($message);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function loadPluginVariant() {
    $plugin = FALSE;
    foreach ($this->getDefinitions() ?: [] as $key => $definition) {
      if (isset($definition['cloud_context'])
        && $definition['cloud_context'] === $this->cloudContext) {
        $plugin = $this->createInstance($key);
        break;
      }
    }
    return $plugin;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginException
   */
  public function loadConfigEntity() {
    $config_entity = $this->plugin->loadConfigEntity($this->cloudContext);
    if ($config_entity === FALSE) {
      $message = $this->t(
        'Cannot load cloud service provider plugin: %cloud_context (CloudConfig::$cloudContext)', [
          '%cloud_context' => $this->cloudContext,
        ]
      );
      throw new CloudConfigPluginException($message);
    }
    return $config_entity;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginException
   */
  public function loadConfigEntities($entity_bundle) {
    /* @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginInterface $plugin */
    $plugin = $this->loadBasePluginDefinition($entity_bundle);
    if ($plugin === FALSE) {
      throw new CloudConfigPluginException($this->t(
        'Cannot load cloud service provider (CloudConfig) entity for %entity_bundle', [
          '%entity_bundle' => $entity_bundle,
        ]
      ));
    }
    return $plugin->loadConfigEntities();
  }

  /**
   * Helper method to load the base plugin definition.
   *
   * Useful when there is no cloud_context.
   *
   * @param string $entity_bundle
   *   The entity bundle.
   *
   * @return bool|object
   *   The base plugin definition.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   Throws the PluginException.
   */
  private function loadBasePluginDefinition($entity_bundle) {
    $plugin = FALSE;
    foreach ($this->getDefinitions() ?: [] as $key => $definition) {
      if (isset($definition['base_plugin']) && $definition['entity_bundle'] === $entity_bundle) {
        $plugin = $this->createInstance($key);
        break;
      }
    }
    return $plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function loadCredentials() {
    return $this->plugin->loadCredentials($this->cloudContext);
  }

  /**
   * {@inheritdoc}
   */
  public function getInstanceCollectionTemplateName() {
    return $this->plugin->getInstanceCollectionTemplateName();
  }

  /**
   * {@inheritdoc}
   */
  public function getPricingPageRoute() {
    return $this->plugin->getPricingPageRoute();
  }

  /**
   * {@inheritdoc}
   */
  public function getServerTemplateCollectionName() {
    return 'entity.cloud_server_template.collection';
  }

  /**
   * {@inheritdoc}
   */
  public function getProjectCollectionName() {
    return 'entity.cloud_project.collection';
  }

}
