<?php

namespace Drupal\cloud_budget\Form\Config;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Cloud budget Admin Settings.
 */
class CloudBudgetAdminSettings extends ConfigFormBase {

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
   * Constructs a CloudBudgetAdminSettings object.
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
    return 'cloud_budget_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['cloud_budget.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('cloud_budget.settings');
    $form['credit_default_amount'] = [
      '#type' => 'number',
      '#title' => "Credit's default amount",
      '#description' => $this->t('The default amount of the credit.'),
      '#default_value' => $config->get('credit_default_amount'),
      '#min' => 1,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable('cloud_budget.settings');
    $config->set('credit_default_amount', $form_state->getValue('credit_default_amount'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
