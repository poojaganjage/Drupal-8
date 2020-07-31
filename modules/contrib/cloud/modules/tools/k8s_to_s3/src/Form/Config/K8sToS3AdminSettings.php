<?php

namespace Drupal\k8s_to_s3\Form\Config;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class K8s to S3 Admin Settings.
 */
class K8sToS3AdminSettings extends ConfigFormBase {

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  private $cloudConfigPluginManager;

  /**
   * Constructs a K8sToS3AdminSettings object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    CloudConfigPluginManagerInterface $cloud_config_plugin_manager
  ) {
    parent::__construct($config_factory);

    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.cloud_config_plugin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'k8s_to_s3_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['k8s_to_s3.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('k8s_to_s3.settings');

    $aws_cloud_configs = $this->cloudConfigPluginManager->loadConfigEntities('aws_cloud');
    $k8s_cloud_configs = $this->cloudConfigPluginManager->loadConfigEntities('k8s');

    $form['k8s'] = [
      '#type' => 'details',
      '#title' => $this->t('K8s'),
      '#open' => TRUE,
    ];

    $k8s_clusters_options = [];
    foreach ($k8s_cloud_configs ?: [] as $k8s_cloud_config) {
      $k8s_clusters_options[$k8s_cloud_config->getCloudContext()] = $k8s_cloud_config->getName();
    }

    $form['k8s']['k8s_clusters'] = [
      '#title' => $this->t('K8s Clusters'),
      '#description' => $this->t('Select K8s clusters whose resources will be saved to S3 bucket.'),
      '#type' => 'checkboxes',
      '#options' => $k8s_clusters_options,
      '#default_value' => empty($config->get('k8s_clusters'))
      ? []
      : json_decode($config->get('k8s_clusters'), TRUE),
      '#required' => TRUE,
    ];

    $form['s3'] = [
      '#type' => 'details',
      '#title' => $this->t('S3'),
      '#open' => TRUE,
    ];

    $aws_clouds_options = [];
    foreach ($aws_cloud_configs ?: [] as $aws_cloud_config) {
      $aws_clouds_options[$aws_cloud_config->getCloudContext()] = $aws_cloud_config->getName();
    }
    $form['s3']['aws_cloud'] = [
      '#title' => $this->t('AWS Cloud'),
      '#description' => $this->t('Select the AWS Cloud whose S3 bucket will be used.'),
      '#type' => 'select',
      '#options' => $aws_clouds_options,
      '#default_value' => $config->get('aws_cloud'),
      '#required' => TRUE,
    ];

    $form['s3']['s3_bucket'] = [
      '#title' => $this->t('Amazon S3 Bucket'),
      '#description' => $this->t('Specify the destination to which the resources of K8s Clusters will be saved to.'),
      '#type' => 'textfield',
      '#default_value' => $config->get('s3_bucket'),
      '#required' => TRUE,
    ];

    $form['docker'] = [
      '#type' => 'details',
      '#title' => $this->t('Docker Registry'),
      '#open' => TRUE,
    ];

    $form['docker']['ecr'] = [
      '#type' => 'details',
      '#title' => $this->t('Amazon ECR'),
      '#open' => TRUE,
    ];

    $form['docker']['ecr']['enable_automatic_ecr_import_export'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Automatically import/export Docker images thru Amazon ECR'),
      '#default_value' => $config->get('enable_automatic_ecr_import_export'),
    ];

    $form['docker']['ecr']['docker_aws_cloud'] = [
      '#title' => $this->t('AWS Cloud'),
      '#description' => $this->t('Select the AWS Cloud whose ECR repository will be used.'),
      '#type' => 'select',
      '#options' => $aws_clouds_options,
      '#default_value' => $config->get('docker_aws_cloud'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable('k8s_to_s3.settings');

    $k8s_clusters = array_filter($form_state->getValue('k8s_clusters'));
    $config->set('k8s_clusters', json_encode($k8s_clusters));

    $config->set('aws_cloud', $form_state->getValue('aws_cloud'));
    $config->set('s3_bucket', $form_state->getValue('s3_bucket'));

    $config->set('enable_automatic_ecr_import_export', $form_state->getValue('enable_automatic_ecr_import_export'));
    $config->set('docker_aws_cloud', $form_state->getValue('docker_aws_cloud'));
    $config->save();

    k8s_to_s3_cron();

    parent::submitForm($form, $form_state);
  }

}
