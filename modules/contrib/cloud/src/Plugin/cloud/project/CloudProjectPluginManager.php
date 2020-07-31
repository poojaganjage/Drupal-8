<?php

namespace Drupal\cloud\Plugin\cloud\project;

use Drupal\cloud\Entity\CloudProjectInterface;
use Drupal\cloud\Plugin\cloud\CloudPluginManager;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides the default cloud_project_plugin manager.
 */
class CloudProjectPluginManager extends CloudPluginManager implements CloudProjectPluginManagerInterface, ContainerInjectionInterface {

  /**
   * Provides default values for all cloud_project_plugin plugins.
   *
   * @var array
   */
  protected $defaults = [
    'id' => 'cloud_project',
    'entity_type' => 'cloud_project',
  ];

  /**
   * Constructs a new CloudProjectPluginManager object.
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

    parent::__construct('Plugin\cloud\project', $namespaces, $module_handler);

    // Add more services as required.
    $this->moduleHandler = $module_handler;
    $this->setCacheBackend($cache_backend, 'cloud_project_plugin', ['cloud_project_plugin']);

  }

  /**
   * Dependency Injection.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Instance of ContainerInterface.
   *
   * @return CloudProjectPluginManager
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
      $this->discovery = new YamlDiscovery('cloud.project.plugin', $this->moduleHandler->getModuleDirectories());
      $this->discovery->addTranslatableProperty('label', 'label_context');
      $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    }
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);

    if (empty($definition['id'])) {
      throw new PluginException(sprintf('CloudProjectPlugin plugin property (%s) definition "is" is required.', $plugin_id));
    }

    if (empty($definition['entity_bundle'])) {
      throw new PluginException(sprintf('entity_bundle property is required for (%s)', $plugin_id));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function loadPluginVariant($cloud_context) {
    $plugin = FALSE;
    foreach ($this->getDefinitions() as $key => $definition) {
      if ($definition['cloud_context'] === $cloud_context) {
        $plugin = $this->createInstance($key);
        break;
      }
    }

    return $plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function launch(CloudProjectInterface $cloud_project, FormStateInterface $form_state = NULL) {
    $plugin = $this->loadPluginVariant($cloud_project->getCloudContext());
    if ($plugin === FALSE) {
      $this->messenger->addStatus($this->t('Cannot load cloud project plugin: %cloud_context', [
        '%cloud_context' => $cloud_project->getCloudContext(),
      ]));

      return [
        'route_name' => 'entity.cloud_project.canonical',
        'params' => [
          'cloud_project' => $cloud_project->id(),
          'cloud_context' => $cloud_project->getCloudContext(),
        ],
      ];
    }

    return $plugin->launch($cloud_project, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildListHeader($cloud_context) {
    $plugin = $this->loadPluginVariant($cloud_context);
    if ($plugin === FALSE) {
      $this->messenger->addStatus($this->t('Cannot load cloud project plugin: %cloud_context', [
        '%cloud_context' => $cloud_context,
      ]));
      return [];
    }

    return $plugin->buildListHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildListRow(CloudProjectInterface $entity) {
    $plugin = $this->loadPluginVariant($entity->getCloudContext());
    if ($plugin === FALSE) {
      $this->messenger->addStatus($this->t('Cannot load cloud project plugin: %cloud_context', [
        '%cloud_context' => $entity->getCloudContext(),
      ]));
      return [];
    }

    return $plugin->buildListRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function updateCloudProjectList($cloud_context) {
    $request = \Drupal::request();
    $referer = $request->headers->get('referer');

    $plugin = $this->loadPluginVariant($cloud_context);

    if ($plugin === FALSE) {
      $this->messenger->addStatus($this->t('Cannot load cloud project plugin: %cloud_context', ['%cloud_context' => $cloud_context]));
    }
    elseif (!method_exists($plugin, 'updateCloudProjectList')) {
      $this->messenger->addStatus($this->t('Unnecessary to update cloud projects.'));
    }
    else {
      $updated = $plugin->updateCloudProjectList($cloud_context);

      if ($updated !== FALSE) {
        $this->messenger->addStatus($this->t('Updated cloud projects.'));
      }
      else {
        $this->messenger->addError($this->t('Unable to update cloud projects.'));
      }
    }
    $url = new Url('entity.cloud_project.collection', [
      'cloud_context' => $cloud_context,
    ]);
    return new RedirectResponse($referer);
  }

}
