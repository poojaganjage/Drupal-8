<?php

namespace Drupal\aws_cloud\Form\Config;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AWS Cloud Admin Notification Settings.
 */
class AwsCloudAdminNotificationSettings extends ConfigFormBase {

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
   * Constructs a AwsCloudAdminNotificationSettings object.
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
    return 'aws_cloud_admin_notification_settings';
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

    $form['instance'] = [
      '#type' => 'details',
      '#title' => $this->t('Instance'),
      '#open' => TRUE,
    ];

    $form['instance']['notification_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Notification Settings'),
      '#open' => TRUE,
    ];

    $form['instance']['notification_settings']['aws_cloud_notification_frequency'] = [
      '#type' => 'select',
      '#options' => [
        86400 => $this->t('Once a day'),
        604800 => $this->t('Once every 7 days'),
        2592000 => $this->t('Once every 1 months'),
        5184000 => $this->t('Once every 2 months'),
        7776000 => $this->t('Once every 3 months'),
        15552000 => $this->t('Once every 6 months'),
        31104000 => $this->t('Once a year'),
      ],
      '#title' => $this->t('Notification frequency'),
      '#description' => $this->t('Instance owners will be notified once per option selected.'),
      '#default_value' => $config->get('aws_cloud_notification_frequency'),
    ];

    $form['instance']['notification_settings']['aws_cloud_instance_notification_fields'] = [
      '#type' => 'fieldgroup',
    ];

    $form['instance']['notification_settings']['aws_cloud_instance_notification_fields']['aws_cloud_instance_notification_title'] = [
      '#type' => 'item',
      '#title' => $this->t('Notification time'),
    ];

    $form['instance']['notification_settings']['aws_cloud_instance_notification_fields']['aws_cloud_instance_notification_hour'] = [
      '#type' => 'select',
      '#prefix' => '<div class="container-inline">',
      '#options' => $this->getDigits(24),
      '#default_value' => $config->get('aws_cloud_instance_notification_hour'),
    ];

    $form['instance']['notification_settings']['aws_cloud_instance_notification_fields']['aws_cloud_instance_notification_minutes'] = [
      '#prefix' => ': ',
      '#type' => 'select',
      '#options' => $this->getDigits(60),
      '#default_value' => $config->get('aws_cloud_instance_notification_minutes'),
      '#suffix' => '</div>' . $this->t('Time to send the instance usage email.'),
    ];

    $form['instance']['long_running_instance_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Long Running EC2 Instance Settings'),
      '#open' => TRUE,
    ];

    $form['instance']['long_running_instance_settings']['aws_cloud_long_running_instance_notification'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable instance notification'),
      '#description' => $this->t('When enabled, instance owners or admins will be notified if their instance has been running for too long.'),
      '#default_value' => $config->get('aws_cloud_long_running_instance_notification'),
    ];

    $form['instance']['long_running_instance_settings']['aws_cloud_long_running_instance_notify_owner'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Notify owner'),
      '#description' => $this->t('When selected, instance owners will be notified.'),
      '#default_value' => $config->get('aws_cloud_long_running_instance_notify_owner'),
    ];

    $form['instance']['long_running_instance_settings']['aws_cloud_long_running_instance_notification_criteria'] = [
      '#type' => 'select',
      '#options' => [
        1 => $this->t('1 day'),
        30 => $this->t('30 days'),
        60 => $this->t('60 days'),
        90 => $this->t('90 days'),
        180 => $this->t('180 days'),
        365 => $this->t('One year'),
      ],
      '#title' => $this->t('Notification criteria'),
      '#description' => $this->t('Notify instance owners after an instance has been running for this period of time.'),
      '#default_value' => $config->get('aws_cloud_long_running_instance_notification_criteria'),
    ];

    $form['instance']['long_running_instance_settings']['aws_cloud_long_running_instance_notification_emails'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email addresses'),
      '#description' => $this->t('Email addresses to be notified.  Emails can be comma separated.'),
      '#default_value' => $config->get('aws_cloud_long_running_instance_notification_emails'),
    ];

    $form['instance']['long_running_instance_settings']['aws_cloud_long_running_instance_notification_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email subject'),
      '#description' => $this->t('Edit the email subject.'),
      '#default_value' => $config->get('aws_cloud_long_running_instance_notification_subject'),
    ];

    $form['instance']['long_running_instance_settings']['aws_cloud_long_running_instance_notification_msg'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Email message'),
      '#default_value' => $config->get('aws_cloud_long_running_instance_notification_msg'),
      '#description' => $this->t('Available tokens are: [aws_cloud_instance:instances], [site:url].  The [aws_cloud_instance:instances] variable can be configured in the Instance information below.'),
    ];

    $form['instance']['long_running_instance_settings']['aws_cloud_long_running_instance_notification_instance_info'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Instance information'),
      '#default_value' => $config->get('aws_cloud_long_running_instance_notification_instance_info'),
      '#description' => $this->t('More than one instance can appear in the email message. Available tokens are: [aws_cloud_instance:name], [aws_cloud_instance:id], [aws_cloud_instance:launch_time], [aws_cloud_instance:instance_state], [aws_cloud_instance:availability_zone], [aws_cloud_instance:private_ip], [aws_cloud_instance:public_up], [aws_cloud_instance:elastic_ip], [aws_cloud_instance:instance_link], [aws_cloud_instance:instance_edit_link].'),
    ];

    $form['instance']['low_utilization_instance_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Low Utilization EC2 Instance Settings'),
      '#open' => TRUE,
    ];

    $form['instance']['low_utilization_instance_settings']['aws_cloud_low_utilization_instance_notification'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable instance notification'),
      '#description' => $this->t('When enabled, instance owners or admins will be notified if their instance has been running for in low utilization status for several days.'),
      '#default_value' => $config->get('aws_cloud_low_utilization_instance_notification'),
    ];

    $form['instance']['low_utilization_instance_settings']['aws_cloud_low_utilization_instance_notify_owner'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Notify owner'),
      '#description' => $this->t('When selected, instance owners will be notified.'),
      '#default_value' => $config->get('aws_cloud_low_utilization_instance_notify_owner'),
    ];

    $form['instance']['low_utilization_instance_settings']['aws_cloud_low_utilization_instance_cpu_utilization_threshold'] = [
      '#type' => 'number',
      '#title' => $this->t('CPU Utilization Threshold'),
      '#description' => $this->t('The CPU utilization thredhold of low utilization instance (The input range is 1-100%; e.g. 10% or less as the default value).'),
      '#size' => 3,
      '#min' => 1,
      '#max' => 100,
      '#field_suffix' => '%',
      '#default_value' => $config->get('aws_cloud_low_utilization_instance_cpu_utilization_threshold'),
    ];

    $form['instance']['low_utilization_instance_settings']['aws_cloud_low_utilization_instance_network_io_threshold'] = [
      '#type' => 'number',
      '#title' => $this->t('Network I/O Threshold'),
      '#description' => $this->t('The network I/O threshold of low utilization instance (The input range is 1-1024MB; e.g. 5MB or less as the default value).'),
      '#size' => 4,
      '#min' => 1,
      '#max' => 1024,
      '#field_suffix' => 'MB',
      '#default_value' => $config->get('aws_cloud_low_utilization_instance_network_io_threshold'),
    ];

    $period_options = [];
    // The period is from 4 to 14 days.
    for ($i = 4; $i <= 14; $i++) {
      $period_options[$i] = $i;
    }
    $form['instance']['low_utilization_instance_settings']['aws_cloud_low_utilization_instance_period'] = [
      '#type' => 'select',
      '#title' => $this->t('Period'),
      '#description' => $this->t('The period of low utilization instance.'),
      '#options' => $period_options,
      '#field_suffix' => 'Days',
      '#default_value' => $config->get('aws_cloud_low_utilization_instance_period'),
    ];

    $form['instance']['low_utilization_instance_settings']['aws_cloud_low_utilization_instance_notification_emails'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email addresses'),
      '#description' => $this->t('Email addresses to be notified.  Emails can be comma separated.'),
      '#default_value' => $config->get('aws_cloud_low_utilization_instance_notification_emails'),
    ];

    $form['instance']['low_utilization_instance_settings']['aws_cloud_low_utilization_instance_notification_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email subject'),
      '#description' => $this->t('Edit the email subject.'),
      '#default_value' => $config->get('aws_cloud_low_utilization_instance_notification_subject'),
    ];

    $form['instance']['low_utilization_instance_settings']['aws_cloud_low_utilization_instance_notification_msg'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Email message'),
      '#default_value' => $config->get('aws_cloud_low_utilization_instance_notification_msg'),
      '#description' => $this->t('Available tokens are: [aws_cloud_instance:instances], [site:url].  The [aws_cloud_instance:instances] variable can be configured in the Instance information below.'),
    ];

    $form['instance']['low_utilization_instance_settings']['aws_cloud_low_utilization_instance_notification_instance_info'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Instance information'),
      '#default_value' => $config->get('aws_cloud_low_utilization_instance_notification_instance_info'),
      '#description' => $this->t('More than one instance can appear in the email message. Available tokens are: [aws_cloud_instance:name], [aws_cloud_instance:id], [aws_cloud_instance:launch_time], [aws_cloud_instance:instance_state], [aws_cloud_instance:availability_zone], [aws_cloud_instance:private_ip], [aws_cloud_instance:public_up], [aws_cloud_instance:elastic_ip], [aws_cloud_instance:instance_link], [aws_cloud_instance:instance_edit_link].'),
    ];

    $form['volume'] = [
      '#type' => 'details',
      '#title' => $this->t('Volume'),
      '#open' => TRUE,
    ];

    $form['volume']['notification_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Notification Settings'),
      '#open' => TRUE,
    ];

    $form['volume']['notification_settings']['aws_cloud_volume_notification'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable volume notification'),
      '#description' => $this->t('When enabled, an email will be sent if volumes are unused.  Additionally, the created date field will be marked in red on the Volume listing page and Volume detail page.'),
      '#default_value' => $config->get('aws_cloud_volume_notification'),
    ];

    $form['volume']['notification_settings']['aws_cloud_volume_notify_owner'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Notify owner'),
      '#description' => $this->t('When selected, volume owners will be notified.'),
      '#default_value' => $config->get('aws_cloud_volume_notify_owner'),
    ];

    $form['volume']['notification_settings']['aws_cloud_volume_notification_frequency'] = [
      '#type' => 'select',
      '#options' => [
        86400 => $this->t('Once a day'),
        604800 => $this->t('Once every 7 days'),
        2592000 => $this->t('Once every 1 months'),
        5184000 => $this->t('Once every 2 months'),
        7776000 => $this->t('Once every 3 months'),
        15552000 => $this->t('Once every 6 months'),
        31104000 => $this->t('Once a year'),
      ],
      '#title' => $this->t('Notification frequency'),
      '#description' => $this->t('Volume notification will be sent once per option selected.'),
      '#default_value' => $config->get('aws_cloud_volume_notification_frequency'),
    ];

    $form['volume']['notification_settings']['aws_cloud_unused_volume_criteria'] = [
      '#type' => 'select',
      '#title' => $this->t('Unused volume criteria'),
      '#description' => $this->t('A volume is considered unused if it has been created and available for the specified number of days.'),
      '#options' => [
        30 => $this->t('30 days'),
        60 => $this->t('60 days'),
        90 => $this->t('90 days'),
        180 => $this->t('180 days'),
        365 => $this->t('One year'),
      ],
      '#default_value' => $config->get('aws_cloud_unused_volume_criteria'),
    ];

    $form['volume']['notification_settings']['aws_cloud_volume_notification_fields'] = [
      '#type' => 'fieldgroup',
    ];

    $form['volume']['notification_settings']['aws_cloud_volume_notification_fields']['aws_cloud_volume_notification_title'] = [
      '#type' => 'item',
      '#title' => $this->t('Notification time'),
    ];

    $form['volume']['notification_settings']['aws_cloud_volume_notification_fields']['aws_cloud_volume_notification_hour'] = [
      '#type' => 'select',
      '#prefix' => '<div class="container-inline">',
      '#options' => $this->getDigits(24),
      '#default_value' => $config->get('aws_cloud_volume_notification_hour'),
    ];

    $form['volume']['notification_settings']['aws_cloud_volume_notification_fields']['aws_cloud_volume_notification_minutes'] = [
      '#prefix' => ': ',
      '#type' => 'select',
      '#options' => $this->getDigits(60),
      '#default_value' => $config->get('aws_cloud_volume_notification_minutes'),
      '#suffix' => '</div>' . $this->t('Time to send the volume usage email.'),
    ];

    $form['volume']['email_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Email Settings'),
      '#open' => TRUE,
    ];

    $form['volume']['email_settings']['aws_cloud_volume_notification_emails'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email addresses'),
      '#description' => $this->t('Email addresses to be notified.  Emails can be comma separated.'),
      '#default_value' => $config->get('aws_cloud_volume_notification_emails'),
    ];

    $form['volume']['email_settings']['aws_cloud_volume_notification_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email subject'),
      '#description' => $this->t('Edit the email subject.'),
      '#default_value' => $config->get('aws_cloud_volume_notification_subject'),
    ];

    $form['volume']['email_settings']['aws_cloud_volume_notification_msg'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Email message'),
      '#default_value' => $config->get('aws_cloud_volume_notification_msg'),
      '#description' => $this->t('Available tokens are: [aws_cloud_volume:volumes], [site:url].  The [aws_cloud_volume:volumes] variable can be configured in the Volume information below.'),
    ];

    $form['volume']['email_settings']['aws_cloud_volume_notification_volume_info'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Volume information'),
      '#default_value' => $config->get('aws_cloud_volume_notification_volume_info'),
      '#description' => $this->t('More than one volume can appear in the email message.  Available tokens are: [aws_cloud_volume:name], [aws_cloud_volume:volume_link], [aws_cloud_volume:created], [aws_cloud_volume:volume_edit_link].'),
    ];

    $form['snapshot'] = [
      '#type' => 'details',
      '#title' => $this->t('Snapshot'),
      '#open' => TRUE,
    ];

    $form['snapshot']['notification_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Notification Settings'),
      '#open' => TRUE,
    ];

    $form['snapshot']['notification_settings']['aws_cloud_snapshot_notification'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable snapshot notification'),
      '#description' => $this->t('When enabled, an email will be sent if snapshot are unused.'),
      '#default_value' => $config->get('aws_cloud_snapshot_notification'),
    ];

    $form['snapshot']['notification_settings']['aws_cloud_snapshot_notify_owner'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Notify owner'),
      '#description' => $this->t('When selected, snapshot owners will be notified.'),
      '#default_value' => $config->get('aws_cloud_snapshot_notify_owner'),
    ];

    $form['snapshot']['notification_settings']['aws_cloud_snapshot_notification_frequency'] = [
      '#type' => 'select',
      '#options' => [
        86400 => $this->t('Once a day'),
        604800 => $this->t('Once every 7 days'),
        2592000 => $this->t('Once every 1 months'),
        5184000 => $this->t('Once every 2 months'),
        7776000 => $this->t('Once every 3 months'),
        15552000 => $this->t('Once every 6 months'),
        31104000 => $this->t('Once a year'),
      ],

      '#title' => $this->t('Notification frequency'),
      '#description' => $this->t('Snapshot notification will be sent once per option selected.'),
      '#default_value' => $config->get('aws_cloud_snapshot_notification_frequency'),
    ];

    $form['snapshot']['notification_settings']['aws_cloud_stale_snapshot_criteria'] = [
      '#type' => 'select',
      '#title' => $this->t('Stale snapshot criteria'),
      '#description' => $this->t('A snapshot is considered stale if it has been created and available for the specified number of days.'),
      '#options' => [
        30 => $this->t('30 days'),
        60 => $this->t('60 days'),
        90 => $this->t('90 days'),
        180 => $this->t('180 days'),
        365 => $this->t('One year'),
      ],
      '#default_value' => $config->get('aws_cloud_stale_snapshot_criteria'),
    ];

    $form['snapshot']['notification_settings']['aws_cloud_snapshot_notification_fields'] = [
      '#type' => 'fieldgroup',
    ];

    $form['snapshot']['notification_settings']['aws_cloud_snapshot_notification_fields']['aws_cloud_snapshot_notification_title'] = [
      '#type' => 'item',
      '#title' => $this->t('Notification time'),
    ];

    $form['snapshot']['notification_settings']['aws_cloud_snapshot_notification_fields']['aws_cloud_snapshot_notification_hour'] = [
      '#type' => 'select',
      '#prefix' => '<div class="container-inline">',
      '#options' => $this->getDigits(24),
      '#default_value' => $config->get('aws_cloud_snapshot_notification_hour'),
    ];

    $form['snapshot']['notification_settings']['aws_cloud_snapshot_notification_fields']['aws_cloud_snapshot_notification_minutes'] = [
      '#prefix' => ': ',
      '#type' => 'select',
      '#options' => $this->getDigits(60),
      '#default_value' => $config->get('aws_cloud_snapshot_notification_minutes'),
      '#suffix' => '</div>' . $this->t('Time to send the snapshot usage email.'),
    ];

    $form['snapshot']['email_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Email Settings'),
      '#open' => TRUE,
    ];

    $form['snapshot']['email_settings']['aws_cloud_snapshot_notification_emails'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email addresses'),
      '#description' => $this->t('Email addresses to be notified.  Emails can be comma separated.'),
      '#default_value' => $config->get('aws_cloud_snapshot_notification_emails'),
    ];

    $form['snapshot']['email_settings']['aws_cloud_snapshot_notification_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email subject'),
      '#description' => $this->t('Edit the email subject.'),
      '#default_value' => $config->get('aws_cloud_snapshot_notification_subject'),
    ];

    $form['snapshot']['email_settings']['aws_cloud_snapshot_notification_msg'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Email message'),
      '#default_value' => $config->get('aws_cloud_snapshot_notification_msg'),
      '#description' => $this->t('Available tokens are: [aws_cloud_snapshot:snapshots], [site:url].  The [aws_cloud_snapshot:snapshots] text is configured in the Snapshot information field below.'),
    ];

    $form['snapshot']['email_settings']['aws_cloud_snapshot_notification_snapshot_info'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Snapshot information'),
      '#default_value' => $config->get('aws_cloud_snapshot_notification_snapshot_info'),
      '#description' => $this->t('More than one snapshot can appear in the email message. Available tokens are: [aws_cloud_snapshot:name], [aws_cloud_snapshot:snapshot_link], [aws_cloud_snapshot:created], [aws_cloud_snapshot:snapshot_edit_link].'),
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

    $instance_time = '';
    $volume_time = '';
    $snapshot_time = '';
    foreach ($form_state->getValues() ?: [] as $key => $value) {
      if ($key === 'aws_cloud_instance_notification_hour') {
        $instance_time .= Html::escape($value);
      }
      if ($key === 'aws_cloud_instance_notification_minutes') {
        $instance_time .= ':' . Html::escape($value);
      }

      if ($key === 'aws_cloud_volume_notification_hour') {
        $volume_time .= Html::escape($value);
      }
      if ($key === 'aws_cloud_volume_notification_minutes') {
        $volume_time .= ':' . Html::escape($value);
      }

      if ($key === 'aws_cloud_snapshot_notification_hour') {
        $snapshot_time .= Html::escape($value);
      }
      if ($key === 'aws_cloud_snapshot_notification_minutes') {
        $snapshot_time .= ':' . Html::escape($value);
      }

      $config->set($key, Html::escape($value));
    }

    if (!empty($instance_time)) {
      // Add seconds into the instance time.
      $config->set('aws_cloud_instance_notification_time', $instance_time . ':00');
    }

    if (!empty($volume_time)) {
      // Add seconds into the volume time.
      $config->set('aws_cloud_volume_notification_time', $volume_time . ':00');
    }

    if (!empty($snapshot_time)) {
      // Add seconds into the snapshot time.
      $config->set('aws_cloud_snapshot_notification_time', $snapshot_time . ':00');
    }

    $config->save();

    // Update spreadsheets.
    $this->updateSpreadsheets($old_config, $config);

    parent::submitForm($form, $form_state);

    if ($this->shouldCacheBeCleaned($old_config, $config)) {
      drupal_flush_all_caches();
    }
  }

  /**
   * Update spreadsheets.
   *
   * @param \Drupal\Core\Config\Config $old_config
   *   The old config object.
   * @param \Drupal\Core\Config\Config $config
   *   The config object.
   */
  private function updateSpreadsheets(Config $old_config, Config $config) {
    $old_spreadsheet_value = $old_config->get('aws_cloud_instance_type_prices_spreadsheet');
    $spreadsheet_value = $config->get('aws_cloud_instance_type_prices_spreadsheet');

    // If the value doesn't change, skip.
    if ($old_spreadsheet_value === $spreadsheet_value) {
      return;
    }

    // If the module gapps isn't installed, skip.
    if (!$this->moduleHandler->moduleExists('gapps')) {
      return;
    }

    // If the credential is invalid, skip.
    if (!gapps_is_google_credential_valid()) {
      return;
    }

    $google_spreadsheet_service = \Drupal::service('gapps.google_spreadsheet');
    $cloud_configs = $this->cloudConfigPluginManager->loadConfigEntities('aws_cloud');
    foreach ($cloud_configs ?: [] as $cloud_config) {
      $old_url = $cloud_config->get('field_spreadsheet_pricing_url')->value;
      if (!empty($old_url)) {
        $google_spreadsheet_service->delete($old_url);
        $cloud_config->set('field_spreadsheet_pricing_url', '');
        $cloud_config->save();
      }
    }
  }

  /**
   * Helper function to generate values in the time drop down.
   *
   * @param int $max
   *   The maximum numbers to generate.
   *
   * @return array
   *   Array of time values.
   */
  private function getDigits($max) {
    $digits = [];
    for ($i = 0; $i < $max; $i++) {
      $digits[sprintf('%02d', $i)] = sprintf('%02d', $i);
    }
    return $digits;
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
