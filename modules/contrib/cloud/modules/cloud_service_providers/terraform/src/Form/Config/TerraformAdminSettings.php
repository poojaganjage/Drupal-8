<?php

namespace Drupal\terraform\Form\Config;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Terraform Admin Settings.
 */
class TerraformAdminSettings extends ConfigFormBase {

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
   * Constructs a TerraformAdminSettings object.
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
    return 'terraform_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['terraform.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('terraform.settings');
    $form['terraform_js_refresh_interval'] = [
      '#type' => 'number',
      '#title' => 'UI Refresh Interval',
      '#description' => $this->t('Refresh UI (Logs and etc) at periodical intervals.'),
      '#default_value' => $config->get('terraform_js_refresh_interval'),
      '#min' => 1,
      '#max' => 9999,
      '#field_suffix' => 'seconds',
    ];

    $form['terraform_update_resources_queue_cron_time'] = [
      '#type' => 'number',
      '#title' => 'Update Resources Queue Cron Time',
      '#description' => $this->t('The cron time for queue update resources.'),
      '#default_value' => $config->get('terraform_update_resources_queue_cron_time'),
      '#min' => 1,
      '#max' => 9999,
      '#field_suffix' => 'seconds',
    ];

    $form['terraform_cloud_config_icon'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Terraform Cloud Config Icon'),
      '#default_value' => [
        'fids' => $config->get('terraform_cloud_config_icon'),
      ],
      '#description' => $this->t('Upload an image to represent Terraform.'),
      '#upload_location' => 'public://images/icons',
      '#upload_validators' => [
        'file_validate_is_image' => [],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable('terraform.settings');
    $config->set('terraform_js_refresh_interval', $form_state->getValue('terraform_js_refresh_interval'));
    $config->set('terraform_update_resources_queue_cron_time', $form_state->getValue('terraform_update_resources_queue_cron_time'));

    $icon = $form_state->getValue('terraform_cloud_config_icon');
    $file = File::load($icon[0]);
    // Save the icon.
    if (!empty($file)) {
      $file->setPermanent();
      $file->save();
      $config->set('terraform_cloud_config_icon', $icon[0]);
    }
    else {
      $config->set('terraform_cloud_config_icon', '');
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
