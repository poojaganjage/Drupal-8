<?php

namespace Drupal\aws_cloud\Form\Config;

use Drupal\aws_cloud\Service\Pricing\PricingService;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AWS Cloud Admin Settings.
 */
class AwsCloudAdminSettings extends ConfigFormBase {

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  private $cloudConfigPluginManager;

  /**
   * Constructs a AwsCloudAdminSettings object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    FileSystemInterface $file_system,
    CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct($config_factory);

    $this->fileSystem = $file_system;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('file_system'),
      $container->get('plugin.manager.cloud_config_plugin'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'aws_cloud_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['aws_cloud.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('aws_cloud.settings');

    $form['test_mode'] = [
      '#type' => 'details',
      '#title' => $this->t('Test Mode'),
      '#open' => TRUE,
    ];

    $form['test_mode']['aws_cloud_test_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable test mode?'),
      '#default_value' => $config->get('aws_cloud_test_mode'),
      '#description' => $this->t('This enables you to test the AWS Cloud module settings without accessing AWS.'),
    ];

    $form['views'] = [
      '#type' => 'details',
      '#title' => $this->t('Views'),
      '#open' => TRUE,
      '#description' => $this->t("Note that selecting the default option will overwrite View's settings."),
    ];

    $form['views']['refresh_options'] = [
      '#type' => 'details',
      '#title' => $this->t('View refresh interval'),
      '#open' => TRUE,
    ];

    $form['views']['refresh_options']['aws_cloud_view_refresh_interval'] = [
      '#type' => 'number',
      '#description' => $this->t('Refresh content of views at periodical intervals.'),
      '#default_value' => $config->get('aws_cloud_view_refresh_interval'),
      '#min' => 1,
      '#max' => 9999,
      '#field_suffix' => 'seconds',
    ];

    $form['views']['pager_options'] = [
      '#type' => 'details',
      '#title' => $this->t('Pager options'),
      '#open' => TRUE,
    ];

    $form['views']['pager_options']['aws_cloud_view_expose_items_per_page'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow user to control the number of items displayed in views.'),
      '#default_value' => $config->get('aws_cloud_view_expose_items_per_page'),
      '#description' => $this->t('When enabled, an "Items per page" dropdown listbox is shown.'),
    ];

    $form['views']['pager_options']['aws_cloud_view_items_per_page'] = [
      '#type' => 'select',
      '#options' => aws_cloud_get_views_items_options(),
      '#title' => $this->t('Items per page'),
      '#description' => $this->t('Number of items to display on each page in views.'),
      '#default_value' => $config->get('aws_cloud_view_items_per_page'),
    ];

    $form['schedule'] = [
      '#type' => 'details',
      '#title' => $this->t('Schedule'),
      '#open' => TRUE,
    ];

    $form['schedule']['termination_options'] = [
      '#type' => 'details',
      '#title' => $this->t('Termination Options'),
      '#open' => TRUE,
    ];

    $form['schedule']['termination_options']['aws_cloud_instance_terminate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Automatically terminate instance'),
      '#description' => $this->t('Terminate instance automatically.'),
      '#default_value' => $config->get('aws_cloud_instance_terminate'),
    ];

    $form['schedule']['schedule_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('AWS Instance Scheduler'),
      '#open' => TRUE,
    ];

    $form['schedule']['schedule_settings']['aws_cloud_scheduler_tag'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Schedule Tag'),
      '#description' => $this->t('Name of scheduling tag. This tag value is defined when setting up the <a href=":stack">AWS Instance Scheduler</a>.', [
        ':stack' => 'https://docs.aws.amazon.com/solutions/latest/instance-scheduler/deployment.html',
      ]),
      '#default_value' => $config->get('aws_cloud_scheduler_tag'),
    ];

    $form['schedule']['schedule_settings']['aws_cloud_scheduler_periods'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Schedule periods'),
      '#description' => $this->t('<p>Schedules defined in AWS Instance Scheduler. The values entered are shown in the Schedule field on instance edit form and launch template launch form. Enter one value per line, in the format <strong>key|label</strong>.</p><p>The key corresponds to the schedule name defined in AWS Instance Scheduler. The label is a free form descriptive value shown to users. An example configuration might be:<br/>office-hours|Office Hours - Monday to Friday 9:00am - 5:00pm.<br/><p>See <a href=:stack>Scheduler Configuration</a> for more information.</p>', [
        ':stack' => 'https://docs.aws.amazon.com/solutions/latest/instance-scheduler/components.html',
      ]),
      '#default_value' => $config->get('aws_cloud_scheduler_periods'),
    ];

    $form['cost_management'] = [
      '#type' => 'details',
      '#title' => $this->t('Cost Management'),
      '#open' => TRUE,
    ];

    $form['cost_management']['aws_cloud_instance_type_prices'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Instance Type Prices'),
      '#description' => $this->t('Enable Instance Type Prices.'),
      '#default_value' => $config->get('aws_cloud_instance_type_prices'),
    ];

    $form['cost_management']['aws_cloud_instance_type_prices_spreadsheet'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Instance Type Prices Spreadsheet'),
      '#description' => $this->t('Enable Instance Type Prices Spreadsheet. If this checkbox is disabled, please install the module %name in %link.',
        [
          '%name' => 'Google Applications',
          '%link' => Link::fromTextAndUrl(
            $this->t('Extend'),
            Url::fromRoute(
              'system.modules_list', []
            )
          )->toString(),
        ]
      ),
      '#default_value' => $config->get('aws_cloud_instance_type_prices_spreadsheet'),
      '#disabled' => !$this->moduleHandler->moduleExists('gapps'),
    ];

    $form['cost_management']['aws_cloud_instance_type_cost'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Instance Type Cost'),
      '#description' => $this->t('Enable Instance Type cost in launch template create or edit form.'),
      '#default_value' => $config->get('aws_cloud_instance_type_cost'),
    ];

    $form['cost_management']['aws_cloud_instance_type_cost_list'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Instance Type Cost List'),
      '#description' => $this->t('Enable Instance Type Cost List in launch template launch form.'),
      '#default_value' => $config->get('aws_cloud_instance_type_cost_list'),
    ];

    $form['cost_management']['aws_cloud_instance_list_cost_column'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Instance List Cost Column'),
      '#description' => $this->t('Enable Cost Column in Instance List.'),
      '#default_value' => $config->get('aws_cloud_instance_list_cost_column'),
    ];

    $form['cost_management']['aws_cloud_ec2_pricing_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('EC2 Pricing Endpoint'),
      '#description' => $this->t('The endpoint of EC2 pricing service. For example, if the path of <code>index.json</code> (EC2 instance type pricing information) is <em>https://example.com/PATH_TO_REGION/REGION/index.json</em> where <em>REGION</em> is such as <em>us-west-2</em>, the value should be <em>https://example.com/PATH_TO_REGION.</em><br/>The default URL is: <em>https://pricing.us-east-1.amazonaws.com/offers/v1.0/aws/AmazonEC2/current</em>'),
      '#size' => 80,
      '#default_value' => $config->get('aws_cloud_ec2_pricing_endpoint'),
    ];

    $cloud_configs = $this->cloudConfigPluginManager->loadConfigEntities('aws_cloud');
    $options[''] = 'N/A';
    foreach ($cloud_configs as $cloud_config) {
      $cloud_context = $cloud_config->getCloudContext();
      $options[$cloud_context] = $cloud_config->getName();
    }

    $form['cost_management']['aws_cloud_service_provider'] = [
      '#type' => 'select',
      '#title' => $this->t('AWS Cloud Service Provider'),
      '#description' => $this->t('Select AWS Cloud Service Provider.'),
      '#options' => $options,
      '#default_value' => $config->get('aws_cloud_service_provider'),
    ];

    $form['price_rate'] = [
      '#type' => 'details',
      '#title' => $this->t('Price Rate'),
      '#description' => $this->t('(Unit: %): The discount rate.'),
      '#open' => TRUE,
    ];

    $form['price_rate']['aws_cloud_price_rate_ec2'] = [
      '#type' => 'number',
      '#title' => $this->t('Amazon EC2'),
      '#min' => 1,
      '#max' => 100,
      '#field_suffix' => '%',
      '#default_value' => $config->get('aws_cloud_price_rate_ec2') ?: 100,
    ];

    $form['monitor'] = [
      '#type' => 'details',
      '#title' => $this->t('Monitor'),
      '#open' => TRUE,
    ];

    $form['monitor']['aws_cloud_monitor_refresh_interval'] = [
      '#type' => 'number',
      '#description' => $this->t('Refresh charts of monitor at periodical intervals.'),
      '#default_value' => $config->get('aws_cloud_monitor_refresh_interval'),
      '#min' => 1,
      '#max' => 9999,
      '#field_suffix' => 'seconds',
    ];

    $form['icon'] = [
      '#type' => 'details',
      '#title' => $this->t('Icon'),
      '#open' => TRUE,
    ];

    $form['icon']['aws_cloud_cloud_config_icon'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('AWS Cloud Config Icon'),
      '#default_value' => [
        'fids' => $config->get('aws_cloud_cloud_config_icon'),
      ],
      '#description' => $this->t('Upload the default image to represent Amazon EC2'),
      '#upload_location' => 'public://images/icons',
      '#upload_validators' => [
        'file_validate_is_image' => [],
      ],
    ];

    $form['cron'] = [
      '#type' => 'details',
      '#title' => $this->t('Cron'),
      '#open' => TRUE,
    ];

    $form['cron']['aws_cloud_update_resources_queue_cron_time'] = [
      '#type' => 'number',
      '#title' => 'Update Resources Queue Cron Time',
      '#description' => $this->t('The cron time for queue update resources.'),
      '#default_value' => $config->get('aws_cloud_update_resources_queue_cron_time'),
      '#min' => 1,
      '#max' => 9999,
      '#field_suffix' => 'seconds',
    ];

    $form['#attached']['library'][] = 'aws_cloud/aws_cloud_view_builder';

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable('aws_cloud.settings');
    $old_config = clone $config;
    $form_state->cleanValues();
    $views_settings = [];
    $form_values = $form_state->getValues();
    $ec2_pricing_endpoint = $form_values['aws_cloud_ec2_pricing_endpoint'] ?? '';
    $config_endpoint = $config->get('aws_cloud_ec2_pricing_endpoint') ?? '';

    $instance_time = '';
    $volume_time = '';
    $snapshot_time = '';
    foreach ($form_state->getValues() ?: [] as $key => $value) {
      if ($key === 'aws_cloud_service_provider') {
        if (!empty($ec2_pricing_endpoint)) {
          ($config_endpoint === $ec2_pricing_endpoint) ? $config->set('aws_cloud_ec2_pricing_endpoint', $config_endpoint) : $config->set('aws_cloud_ec2_pricing_endpoint', $ec2_pricing_endpoint);
        }
        else {
          $config->set('aws_cloud_ec2_pricing_endpoint', PricingService::DEFAULT_ENDPOINT);
        }
      }

      if ($key === 'aws_cloud_view_items_per_page') {
        $views_settings[$key] = (int) $value;
      }
      elseif ($key === 'aws_cloud_view_expose_items_per_page') {
        $views_settings[$key] = (boolean) $value;
      }

      if ($key === 'aws_cloud_cloud_config_icon') {
        $icon = $form_state->getValue('aws_cloud_cloud_config_icon');
        $file = File::load($icon[0]);
        // Save the icon.
        if (!empty($file)) {
          $file->setPermanent();
          $file->save();
          $config->set('aws_cloud_cloud_config_icon', $icon[0]);
        }
        else {
          $config->set('aws_cloud_cloud_config_icon', '');
        }
        continue;
      }

      $config->set($key, Html::escape($value));
    }

    $config->save();

    if (!empty($views_settings)) {
      $this->updateViewsPagerOptions($views_settings);
    }

    parent::submitForm($form, $form_state);

  }

  /**
   * Update views pager options.
   *
   * @param array $views_settings
   *   The key and value array of views pager options.
   */
  private function updateViewsPagerOptions(array $views_settings) {
    $views = [
      'views.view.aws_cloud_key_pair',
      'views.view.aws_cloud_elastic_ip',
      'views.view.aws_cloud_image',
      'views.view.aws_cloud_instance',
      'views.view.aws_cloud_network_interface',
      'views.view.aws_cloud_security_group',
      'views.view.aws_cloud_snapshot',
      'views.view.aws_cloud_volume',
    ];

    $options = [];
    foreach ($views_settings ?: [] as $key => $value) {
      $view_key = str_replace('aws_cloud_view_', '', $key);
      if (strpos($view_key, 'expose_') !== FALSE) {
        $view_key = str_replace('expose_', 'expose.', $view_key);
        if ($value) {
          $items_per_page = aws_cloud_get_views_items_options();
          $options['display.default.display_options.pager.options.expose.items_per_page_options'] = implode(',', $items_per_page);
          $options['display.default.display_options.pager.options.expose.items_per_page_options_all'] = TRUE;
        }
      }
      $options["display.default.display_options.pager.options.$view_key"] = $value;

    }
    foreach ($views ?: [] as $view_name) {
      aws_cloud_update_views_configuration($view_name, $options);
    }
  }

  /**
   * Judge whether cache should be cleaned or not.
   *
   * @param \Drupal\Core\Config\Config $old_config
   *   The old config object.
   * @param \Drupal\Core\Config\Config $config
   *   The config object.
   *
   * @return bool
   *   Whether cache should be cleaned or not.
   */
  private function shouldCacheBeCleaned(Config $old_config, Config $config) {
    $items = [
      'aws_cloud_instance_type_prices',
      'aws_cloud_instance_type_prices_spreadsheet',
      'aws_cloud_instance_type_cost',
      'aws_cloud_instance_type_cost_list',
      'aws_cloud_instance_list_cost_column',
    ];

    foreach ($items ?: [] as $item) {
      if ($old_config->get($item) !== $config->get($item)) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
