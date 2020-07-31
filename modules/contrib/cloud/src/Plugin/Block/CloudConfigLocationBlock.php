<?php

namespace Drupal\cloud\Plugin\Block;

use Drupal\cloud\Service\CloudServiceInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a CloudConfig location map block.
 *
 * @Block(
 *   id = "cloud_config_location",
 *   admin_label = @Translation("Cloud Service Provider Location Map"),
 *   category = @Translation("Cloud")
 * )
 */
class CloudConfigLocationBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\cloud\Service\CloudServiceInterface
   */
  protected $cloudService;

  /**
   * Constructs a new CloudConfigLocationBlock instance.
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
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match service.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $kill_switch
   *   The page cache kill switch service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\cloud\Service\CloudServiceInterface $cloud_service
   *   The Cloud service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RouteMatchInterface $route_match,
    KillSwitch $kill_switch,
    ModuleHandlerInterface $module_handler,
    ConfigFactoryInterface $config_factory,
    Request $request,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    CloudServiceInterface $cloud_service
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeMatch = $route_match;
    $this->killSwitch = $kill_switch;
    $this->moduleHandler = $module_handler;
    $this->configFactory = $config_factory;
    $this->request = $request;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->cloudService = $cloud_service;
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
      $container->get('current_route_match'),
      $container->get('page_cache_kill_switch'),
      $container->get('module_handler'),
      $container->get('config.factory'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('entity_type.bundle.info'),
      $container->get('cloud')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['cloud_service_provider'] = [
      '#type' => 'select',
      '#title' => $this->t('Cloud Service Provider'),
      '#description' => $this->t('Select cloud service provider.'),
      '#options' => $this->getCloudServiceProviders(),
      '#default_value' => $config['cloud_service_provider'] ?? '',
      '#description' => $this->t('Choose a cloud service provider to display on
      the map. Leave blank to display all'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    if (!empty($form_state->getValue('cloud_service_provider'))) {
      $this->configuration['cloud_service_provider'] = $form_state->getValue('cloud_service_provider');
    }
    else {
      unset($this->configuration['cloud_service_provider']);
    }
  }

  /**
   * Get the enabled cloud service providers.
   *
   * @return array
   *   An array of providers.
   */
  private function getCloudServiceProviders() {
    $providers = [
      '' => 'Show all',
    ];
    $bundles = $this->entityTypeBundleInfo->getBundleInfo('cloud_config');
    if (count($bundles)) {
      foreach ($bundles ?: [] as $key => $bundle) {
        $providers[$key] = $bundle['label'];
      }
    }
    return $providers;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $route_name = $this->routeMatch->getRouteName();

    if ($route_name === 'views.ajax') {
      // When the request is from ajax, get the cloud context from referer.
      global $base_url;
      // Get the referer url.
      $referer = $this->request->headers->get('referer');
      if (!empty($referer)) {
        // Get the alias or the referer.
        $alias = substr($referer, strlen($base_url));
        $url = Url::fromUri('internal:' . $alias);
        $route_name = $url->getRouteName();
      }
    }

    if (isset($this->configuration['not_display_cloud_config_page'])
      && $this->configuration['not_display_cloud_config_page'] === TRUE) {
      if ($route_name !== 'view.cloud_config.list'
      && $route_name !== 'entity.cloud_config.collection'
      && $route_name !== 'entity.cloud_config.canonical') {
        return [];
      }
    }
    elseif ($route_name === 'view.cloud_config.list'
      || $route_name === 'entity.cloud_config.collection'
      || $route_name === 'entity.cloud_config.canonical') {
      return [];
    }

    $fieldset_defs = [
      [
        'name' => 'cloud_config_location',
        'title' => $this->t('Location Map'),
        'open' => TRUE,
        'fields' => [
          'cloud_config_location_map',
        ],
      ],
    ];

    if ($route_name === 'entity.cloud_config.canonical') {
      $cloud_config = $this->routeMatch->getParameter('cloud_config');
      $url = Url::fromRoute('entity.cloud_config.location', ['cloud_config' => $cloud_config->id()])->toString();
    }
    else {
      $params = [];
      if (isset($this->configuration['cloud_service_provider'])) {
        $params['cloud_service_provider'] = $this->configuration['cloud_service_provider'];
      }
      $url = Url::fromRoute('entity.cloud_config.locations', $params)->toString();
    }

    $config = $this->configFactory->get('cloud.settings');
    $map_json_url = !empty($config->get('cloud_use_default_urls'))
        ? $config->get('cloud_default_location_map_json_url')
        : $config->get('cloud_custom_location_map_json_url');

    $build = [];

    $build['cloud_config_location_map'] = [
      '#markup' => '<div id="cloud_config_location"></div>',
      '#attached' => [
        'library' => [
          'cloud/cloud_config_location',
        ],
        'drupalSettings' => [
          'cloud' => [
            'cloud_location_map_json_url' => $map_json_url,
            'cloud_config_location_json_url' => $url,
          ],
        ],
      ],
    ];

    \Drupal::service('cloud')->reorderForm($build, $fieldset_defs);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    $this->killSwitch->trigger();
    // BigPipe/#cache/max-age is breaking my block javascript
    // https://www.drupal.org/forum/support/module-development-and-code-questions/2016-07-17/bigpipecachemax-age-is-breaking-my
    // "a slight delay of a second or two before the charts are built.
    // That seems to work, though it is a janky solution.".
    return $this->moduleHandler->moduleExists('big_pipe') ? 1 : 0;
  }

}
