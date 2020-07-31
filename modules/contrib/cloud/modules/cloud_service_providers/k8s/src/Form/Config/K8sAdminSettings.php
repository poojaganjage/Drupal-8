<?php

namespace Drupal\k8s\Form\Config;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Plugin\Field\FieldType\FileItem;

/**
 * Class Kubernetes Admin Settings.
 */
class K8sAdminSettings extends ConfigFormBase {

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * The cloud service provider plugin manager.
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  private $cloudConfigPluginManager;

  /**
   * Constructs a K8sAdminSettings object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    FileSystemInterface $file_system,
    CloudConfigPluginManagerInterface $cloud_config_plugin_manager
  ) {
    parent::__construct($config_factory);

    $this->fileSystem = $file_system;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('file_system'),
      $container->get('plugin.manager.cloud_config_plugin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'k8s_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['k8s.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('k8s.settings');
    $form['k8s_js_refresh_interval'] = [
      '#type' => 'number',
      '#title' => 'UI Refresh Interval',
      '#description' => $this->t('Refresh UI (Charts and etc) at periodical intervals.'),
      '#default_value' => $config->get('k8s_js_refresh_interval'),
      '#min' => 1,
      '#max' => 9999,
      '#field_suffix' => 'seconds',
    ];

    $form['k8s_update_resources_queue_cron_time'] = [
      '#type' => 'number',
      '#title' => 'Update Resources Queue Cron Time',
      '#description' => $this->t('The cron time for queue update resources.'),
      '#default_value' => $config->get('k8s_update_resources_queue_cron_time'),
      '#min' => 1,
      '#max' => 9999,
      '#field_suffix' => 'seconds',
    ];

    $form['k8s_update_cost_storage_queue_cron_time'] = [
      '#type' => 'number',
      '#title' => 'Update Cost Storage Queue Cron Time',
      '#description' => $this->t('The cron time for queue update cost storage.'),
      '#default_value' => $config->get('k8s_update_cost_storage_queue_cron_time'),
      '#min' => 1,
      '#max' => 9999,
      '#field_suffix' => 'seconds',
    ];

    $form['k8s_update_resource_storage_queue_cron_time'] = [
      '#type' => 'number',
      '#title' => 'Update Resource Storage Queue Cron Time',
      '#description' => $this->t('The cron time for queue update resource storage.'),
      '#default_value' => $config->get('k8s_update_resource_storage_queue_cron_time'),
      '#min' => 1,
      '#max' => 9999,
      '#field_suffix' => 'seconds',
    ];

    $form['k8s_keep_resource_storage_time_period'] = [
      '#type' => 'number',
      '#title' => 'The time range to keep resources data.',
      '#description' => $this->t('The time range to keep resources data of all namespaces.'),
      '#default_value' => $config->get('k8s_update_resource_storage_queue_cron_time'),
      '#min' => 1,
      '#max' => 12,
      '#field_suffix' => 'month',
    ];

    $form['k8s_cloud_config_icon'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('K8s Cloud Config Icon'),
      '#default_value' => [
        'fids' => $config->get('k8s_cloud_config_icon'),
      ],
      '#description' => $this->t('Upload an image to represent K8s.'),
      '#upload_location' => 'public://images/icons',
      '#upload_validators' => [
        'file_validate_is_image' => [],
      ],
    ];

    $extensions = str_replace(' ', ', ', $config->get('k8s_yaml_file_extensions'));
    $form['k8s_yaml_file_extensions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed YAML file extensions'),
      '#default_value' => $extensions,
      '#description' => $this->t('Separate extensions with a space or comma and do not include the leading dot.'),
      '#element_validate' => [[FileItem::class, 'validateExtensions']],
      '#weight' => 1,
      '#maxlength' => 256,
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable('k8s.settings');
    $config->set('k8s_js_refresh_interval', $form_state->getValue('k8s_js_refresh_interval'));
    $config->set('k8s_update_resources_queue_cron_time', $form_state->getValue('k8s_update_resources_queue_cron_time'));
    $config->set('k8s_update_cost_storage_queue_cron_time', $form_state->getValue('k8s_update_cost_storage_queue_cron_time'));
    $config->set('k8s_update_resource_storage_queue_cron_time', $form_state->getValue('k8s_update_resource_storage_queue_cron_time'));
    $config->set('k8s_keep_resource_storage_time_range', $form_state->getValue('k8s_keep_resource_storage_time_range'));
    $config->set('k8s_yaml_file_extensions', $form_state->getValue('k8s_yaml_file_extensions'));

    $icon = $form_state->getValue('k8s_cloud_config_icon');
    $file = File::load($icon[0]);
    // Save the icon.
    if (!empty($file)) {
      $file->setPermanent();
      $file->save();
      $config->set('k8s_cloud_config_icon', $icon[0]);
    }
    else {
      $config->set('k8s_cloud_config_icon', '');
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
