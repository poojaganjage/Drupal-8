<?php

namespace Drupal\aws_cloud\Controller\Ec2;

use Drupal\aws_cloud\Service\Pricing\InstanceTypePriceTableRenderer;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller responsible to show price list.
 */
class InstanceTypePriceController extends ControllerBase implements InstanceTypePriceControllerInterface {

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * The route builder.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routeBuilder;

  /**
   * InstanceTypePriceController constructor.
   *
   * @param \Drupal\aws_cloud\Service\Pricing\InstanceTypePriceTableRenderer $price_table_renderer
   *   AWS Pricing service.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   * @param \Drupal\Core\Routing\RouteBuilderInterface $route_builder
   *   The route builder.
   */
  public function __construct(InstanceTypePriceTableRenderer $price_table_renderer, CloudConfigPluginManagerInterface $cloud_config_plugin_manager, RouteBuilderInterface $route_builder) {
    $this->priceTableRenderer = $price_table_renderer;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
    $this->routeBuilder = $route_builder;
  }

  /**
   * Dependency Injection.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Instance of ContainerInterface.
   *
   * @return InstanceTypePriceController
   *   return created object.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('aws_cloud.instance_type_price_table_renderer'),
      $container->get('plugin.manager.cloud_config_plugin'),
      $container->get('router.builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function show($cloud_context) {
    $build = [];

    $build['table'] = $this->priceTableRenderer->render($cloud_context);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function update($cloud_context) {
    // Load the cloud config.
    $this->cloudConfigPluginManager->setCloudContext($cloud_context);
    $cloud_config = $this->cloudConfigPluginManager->loadConfigEntity();

    // Update instance types.
    if (!empty(aws_cloud_update_instance_types($cloud_config, TRUE))) {
      // Rebuild the route.
      $this->routeBuilder->rebuild();

      // NOTE: $this->messenger() is correct.
      // cf. MessengerTrait::messenger() MessengerInterface.
      $this->messenger()->addStatus($this->t('Updated Instance Type Prices.'));
    }

    // Redirect to the price list.
    return $this->redirect('aws_cloud.instance_type_prices', [
      'cloud_context' => $cloud_context,
    ]);
  }

}
