<?php

namespace Drupal\cloud\Form\Config;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\geocoder\ProviderPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Cloud Admin Settings.
 */
class CloudAdminSettings extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The geocoder provider plugin manager.
   *
   * @var \Drupal\geocoder\ProviderPluginManager
   */
  protected $geocoderProviderPluginManager;

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructs a CloudAdminSettings instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   * @param \Drupal\geocoder\ProviderPluginManager $geocoder_provider_plugin_manager
   *   The geocoder provider plugin manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    ModuleHandlerInterface $module_handler,
    UrlGeneratorInterface $url_generator,
    ProviderPluginManager $geocoder_provider_plugin_manager = NULL
  ) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
    $this->urlGenerator = $url_generator;
    $this->geocoderProviderPluginManager = $geocoder_provider_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('url_generator'),
      $container->has('plugin.manager.geocoder.provider') ? $container->get('plugin.manager.geocoder.provider') : NULL
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cloud_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['cloud.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('cloud.settings');

    $form['custom_urls'] = [
      '#type' => 'details',
      '#title' => $this->t('Custom URLs'),
      '#open' => TRUE,
    ];

    $form['custom_urls']['cloud_use_default_urls'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Default Settings'),
      '#default_value' => $config->get('cloud_use_default_urls'),
      '#description' => $this->t('Uncheck if you want to customize the default JavaScript URLs to your own.'),
    ];

    $form['custom_urls']['cloud_custom_location_map_json_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Location Map JSON URL'),
      '#default_value' => $config->get('cloud_custom_location_map_json_url'),
      '#description' => $this->t('The default URL is <em>https://enjalot.github.io/wwsd/data/world/ne_50m_admin_0_countries.geojson</em>.'),
      '#states' => [
        'visible' => [
          'input[name="cloud_use_default_urls"]' => ['checked' => FALSE],
        ],
      ],
    ];

    if ($this->moduleHandler->moduleExists('geocoder')) {
      $form['geocoder'] = [
        '#type' => 'details',
        '#title' => $this->t('Geocoder'),
        '#open' => TRUE,
      ];

      $providers = $this->geocoderProviderPluginManager->getPlugins();
      $options = [];
      foreach ($providers ?: [] as $key => $values) {
        $options[$key] = $values['name'];
      }

      $form['geocoder']['cloud_location_geocoder_plugin'] = [
        '#type' => 'select',
        '#title' => $this->t('Geocoder Plugin'),
        '#options' => $options,
        '#default_value' => $config->get('cloud_location_geocoder_plugin'),
        '#description' => $this->t('The Geocoder plugin to get latitude and longitude of cloud service provider location. Please confirm the options / arguments of selected Geocoder plugin are set at <a href=":url">Geocoder configuration</a>.',
          [':url' => $this->urlGenerator->generate('geocoder.settings')]),
      ];
    }

    $form['custom_urls']['cloud_custom_d3_js_url'] = [
      '#type' => 'url',
      '#title' => $this->t('D3.js JavaScript URL'),
      '#default_value' => $config->get('cloud_custom_d3_js_url'),
      '#description' => $this->t('The default URL is <em>https://d3js.org/d3.v5.min.js</em>.'),
      '#states' => [
        'visible' => [
          'input[name="cloud_use_default_urls"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['custom_urls']['cloud_custom_c3_js_url'] = [
      '#type' => 'url',
      '#title' => $this->t('C3.js JavaScript URL'),
      '#default_value' => $config->get('cloud_custom_c3_js_url'),
      '#description' => $this->t('The default URL is <em>https://cdnjs.cloudflare.com/ajax/libs/c3/0.7.15/c3.min.js</em>.'),
      '#states' => [
        'visible' => [
          'input[name="cloud_use_default_urls"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['custom_urls']['cloud_custom_c3_css_url'] = [
      '#type' => 'url',
      '#title' => $this->t('C3.js CSS URL'),
      '#default_value' => $config->get('cloud_custom_c3_css_url'),
      '#description' => $this->t('The default URL is <em>https://cdnjs.cloudflare.com/ajax/libs/c3/0.7.15/c3.min.css</em>.'),
      '#states' => [
        'visible' => [
          'input[name="cloud_use_default_urls"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['custom_urls']['cloud_custom_chart_js_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Chart.js JavaScript URL'),
      '#default_value' => $config->get('cloud_custom_chart_js_url'),
      '#description' => $this->t('The default URL is <em>https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js</em>.'),
      '#states' => [
        'visible' => [
          'input[name="cloud_use_default_urls"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['custom_urls']['cloud_custom_select2_css_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Select2 CSS URL'),
      '#default_value' => $config->get('cloud_custom_select2_css_url'),
      '#description' => $this->t('The default URL is <em>https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css</em>.'),
      '#states' => [
        'visible' => [
          'input[name="cloud_use_default_urls"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['custom_urls']['cloud_custom_select2_js_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Select2 JavaScript URL'),
      '#default_value' => $config->get('cloud_custom_select2_js_url'),
      '#description' => $this->t('The default URL is <em>https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js</em>.'),
      '#states' => [
        'visible' => [
          'input[name="cloud_use_default_urls"]' => ['checked' => FALSE],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $d3_url = $form_state->getValue('cloud_custom_d3_js_url');
    $c3_js_url = $form_state->getValue('cloud_custom_c3_js_url');
    $c3_css_url = $form_state->getValue('cloud_custom_c3_css_url');
    $chartjs_url = $form_state->getValue('cloud_custom_chart_js_url');
    $select2_css_url = $form_state->getValue('cloud_custom_select2_css_url');
    $select2_js_url = $form_state->getValue('cloud_custom_select2_js_url');

    if (empty($d3_url)
    || empty($c3_js_url)
    || empty($c3_css_url)
    || empty($chartjs_url)
    || empty($select2_css_url)
    || empty($select2_js_url)) {
      $form_state->setErrorByName('cloud_custom_d3_js_url', $this->t('Please enter D3.js JavaScript URL.'));
      $form_state->setErrorByName('cloud_custom_c3_js_url', $this->t('Please enter C3.js JavaScript URL.'));
      $form_state->setErrorByName('cloud_custom_c3_css_url', $this->t('Please enter C3.js CSS URL.'));
      $form_state->setErrorByName('cloud_custom_chart_js_url', $this->t('Please enter Chart.js JavaScript URL.'));
      $form_state->setErrorByName('cloud_custom_select2_js_url', $this->t('Please enter Select2 JavaScript URL.'));
      $form_state->setErrorByName('cloud_custom_select2_css_url', $this->t('Please enter Select2 CSS URL.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->configFactory()->getEditable('cloud.settings');

    $config->set('cloud_use_default_urls', $form_state->getValue('cloud_use_default_urls'));
    $config->set('cloud_custom_location_map_json_url', $form_state->getValue('cloud_custom_location_map_json_url'));
    $config->set('cloud_custom_d3_js_url', $form_state->getValue('cloud_custom_d3_js_url'));
    $config->set('cloud_custom_c3_js_url', $form_state->getValue('cloud_custom_c3_js_url'));
    $config->set('cloud_custom_c3_css_url', $form_state->getValue('cloud_custom_c3_css_url'));
    $config->set('cloud_custom_chart_js_url', $form_state->getValue('cloud_custom_chart_js_url'));
    $config->set('cloud_custom_select2_js_url', $form_state->getValue('cloud_custom_select2_js_url'));
    $config->set('cloud_custom_select2_css_url', $form_state->getValue('cloud_custom_select2_css_url'));

    if ($this->moduleHandler->moduleExists('geocoder')) {
      $config->set('cloud_location_geocoder_plugin', $form_state->getValue('cloud_location_geocoder_plugin'));
    }
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
