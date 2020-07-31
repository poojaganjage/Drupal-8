<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Service\EntityLinkRendererInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Plugin\CachedDiscoveryClearerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Render\Renderer;
use Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for monitor of a Instance entity.
 *
 * @ingroup aws_cloud
 */
class InstanceMonitorForm extends AwsCloudContentForm {

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * InstanceMonitorForm constructor.
   *
   * @param \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface $ec2_service
   *   The AWS Cloud or OpenStack EC2 Service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The Messenger Service.
   * @param \Drupal\cloud\Service\EntityLinkRendererInterface $entity_link_renderer
   *   The entity link render service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The Entity Type Manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheRender
   *   A cache backend interface instance.
   * @param \Drupal\Core\Plugin\CachedDiscoveryClearerInterface $plugin_cache_clearer
   *   A plugin cache clear instance.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The general renderer.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(Ec2ServiceInterface $ec2_service,
                              EntityRepositoryInterface $entity_repository,
                              EntityTypeBundleInfoInterface $entity_type_bundle_info,
                              TimeInterface $time,
                              Messenger $messenger,
                              EntityLinkRendererInterface $entity_link_renderer,
                              EntityTypeManager $entity_type_manager,
                              CacheBackendInterface $cacheRender,
                              CachedDiscoveryClearerInterface $plugin_cache_clearer,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
                              AccountInterface $current_user,
                              RouteMatchInterface $route_match,
                              DateFormatterInterface $date_formatter,
                              Renderer $renderer,
                              ConfigFactoryInterface $config_factory) {
    parent::__construct(
      $ec2_service,
      $entity_repository,
      $entity_type_bundle_info,
      $time,
      $messenger,
      $entity_link_renderer,
      $entity_type_manager,
      $cacheRender,
      $plugin_cache_clearer,
      $cloud_config_plugin_manager,
      $current_user,
      $route_match,
      $date_formatter,
      $renderer
    );

    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('aws_cloud.ec2'),
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('messenger'),
      $container->get('entity.link_renderer'),
      $container->get('entity_type.manager'),
      $container->get('cache.render'),
      $container->get('plugin.cache_clearer'),
      $container->get('plugin.manager.cloud_config_plugin'),
      $container->get('current_user'),
      $container->get('current_route_match'),
      $container->get('date.formatter'),
      $container->get('renderer'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    $entity = $this->entity;

    $form['cpu'] = [
      '#type' => 'details',
      '#title' => $this->t('CPU Utilization (Percent)'),
      '#open' => TRUE,
    ];

    $form['cpu']['cpu_chart'] = [
      '#type' => 'container',
    ];

    $form['network'] = [
      '#type' => 'details',
      '#title' => $this->t('Network Traffic (Megabytes)'),
      '#open' => TRUE,
    ];

    $form['network']['network_chart'] = [
      '#type' => 'container',
    ];

    $form['disk'] = [
      '#type' => 'details',
      '#title' => $this->t('Disk (Bytes)'),
      '#open' => TRUE,
    ];

    $form['disk']['disk_chart'] = [
      '#type' => 'container',
    ];

    $form['disk_operation'] = [
      '#type' => 'details',
      '#title' => $this->t('Disk (Operations)'),
      '#open' => TRUE,
    ];

    $form['disk_operation']['disk_operation_chart'] = [
      '#type' => 'container',
    ];

    $form['#attached']['library'][] = 'aws_cloud/aws_cloud_instance_monitor';
    $form['#attached']['drupalSettings']['aws_cloud_monitor_refresh_interval']
      = $this->configFactory
        ->get('aws_cloud.settings')
        ->get('aws_cloud_monitor_refresh_interval');

    return $form;
  }

}
