<?php

namespace Drupal\openstack\Plugin\Derivative;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides plugin definitions for custom local menu for all resources.
 *
 * @see \Drupal\openstack\Plugin\Derivative\OpenStackAllResourcesLocalTasks
 */
class OpenStackAllResourcesLocalTasks extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * Constructs new OpenStackLocalTasks.
   *
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The config plugin manager.
   */
  public function __construct(CloudConfigPluginManagerInterface $cloud_config_plugin_manager) {
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('plugin.manager.cloud_config_plugin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $entities = $this->cloudConfigPluginManager->loadConfigEntities('openstack');

    foreach ($entities ?: [] as $entity) {
      /* @var \Drupal\cloud\Entity\CloudConfig $entity */
      $id = $entity->id() . '.local_tasks.' . $entity->getCloudContext() . '.all';
      $this->derivatives[$id] = $base_plugin_definition;
      $this->derivatives[$id]['title'] = $entity->label();
      $this->derivatives[$id]['route_name'] = 'view.openstack_instance.all';
    }

    return $this->derivatives;
  }

}
