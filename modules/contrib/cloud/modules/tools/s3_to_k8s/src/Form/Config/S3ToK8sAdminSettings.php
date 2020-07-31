<?php

namespace Drupal\s3_to_k8s\Form\Config;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\k8s\Service\K8sServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class S3 to K8s Admin Settings.
 */
class S3ToK8sAdminSettings extends ConfigFormBase {

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  private $cloudConfigPluginManager;

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The K8s Service.
   *
   * @var \Drupal\k8s\Service\K8sServiceInterface
   */
  private $k8sService;

  /**
   * Constructs a S3ToK8sAdminSettings object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   * @param \Drupal\k8s\Service\K8sServiceInterface $k8s_service
   *   The k8s service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
    UrlGeneratorInterface $url_generator,
    K8sServiceInterface $k8s_service
  ) {
    parent::__construct($config_factory);

    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
    $this->urlGenerator = $url_generator;
    $this->k8sService = $k8s_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.cloud_config_plugin'),
      $container->get('url_generator'),
      $container->get('k8s')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 's3_to_k8s_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['s3_to_k8s.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('s3_to_k8s.settings');

    $aws_cloud_configs = $this->cloudConfigPluginManager->loadConfigEntities('aws_cloud');
    $k8s_cloud_configs = $this->cloudConfigPluginManager->loadConfigEntities('k8s');

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
      '#description' => $this->t('Specify the source bucket of Amazon S3 will be imported from which the resources of K8s Cluster.'),
      '#type' => 'textfield',
      '#default_value' => $config->get('s3_bucket'),
      '#required' => TRUE,
    ];

    $form['s3']['s3_path'] = [
      '#title' => $this->t('Amazon S3 Path'),
      '#description' => $this->t('Specify the source path in Amazon S3 bucket will be imported from which the resources of K8s Cluster.<br />You can find the source path name as the Cloud Service Provider ID in <a href=":url">K8s Profile</a>.',
        [':url' => $this->urlGenerator->generate('view.k8s_profile.list')]),
      '#type' => 'textfield',
      '#default_value' => $config->get('s3_path'),
      '#required' => TRUE,
    ];

    $form['k8s'] = [
      '#type' => 'details',
      '#title' => $this->t('K8s'),
      '#open' => TRUE,
    ];

    $k8s_clusters_options = ['Automatic' => $this->t('&lt;Automatic&gt;')];
    foreach ($k8s_cloud_configs ?: [] as $k8s_cloud_config) {
      $k8s_clusters_options[$k8s_cloud_config->getCloudContext()] = $k8s_cloud_config->getName();
    }

    $form['k8s']['k8s_cluster'] = [
      '#title' => $this->t('K8s Cluster'),
      '#description' => $this->t('Select the K8s cluster to which the K8s resources will be exported.
      <br />If <code>&lt;Automatic&gt;</code> is selected, the K8s resources will be automatically deployed onto a cluster of the minimum resource usage.
      <br />Automatic allocation will be applied if @metrics_server_link is installed on the the clusters', ['@metrics_server_link' => $this->k8sService->getMetricsServerLink()]),
      '#type' => 'select',
      '#options' => $k8s_clusters_options,
      '#default_value' => empty($config->get('k8s_cluster'))
      ? 'Automatic'
      : $config->get('k8s_cluster'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable('s3_to_k8s.settings');
    $config->set('aws_cloud', $form_state->getValue('aws_cloud'));
    $config->set('s3_bucket', $form_state->getValue('s3_bucket'));
    $config->set('s3_path', $form_state->getValue('s3_path'));
    $config->set('k8s_cluster', $form_state->getValue('k8s_cluster'));
    $config->save();
    s3_to_k8s_cron();

    parent::submitForm($form, $form_state);
  }

}
