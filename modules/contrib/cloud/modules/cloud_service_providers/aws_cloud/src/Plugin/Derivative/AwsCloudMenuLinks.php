<?php

namespace Drupal\aws_cloud\Plugin\Derivative;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides plugin definitions for custom local menu.
 *
 * @see \Drupal\aws_cloud\Plugin\Derivative\AwsCloudLocalTasks
 */
class AwsCloudMenuLinks extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs new AwsCloudLocalTasks.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
    ModuleHandlerInterface $module_handler) {
    $this->entityTypeManager = $entity_type_manager;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.cloud_config_plugin'),
      $container->get('module_handler')
    );
  }

  /**
   * Add a tertiary level menu.
   *
   * @param array $base_plugin_definition
   *   An array of the base_plugin_definition.
   * @param string $cloud_context
   *   The cloud context.
   * @param string $entity_id
   *   The entity ID.
   * @param array $link_data
   *   An array of link data.
   *
   * @return array
   *   An array of menu link definitions.
   */
  private function addChildLink(array $base_plugin_definition, $cloud_context, $entity_id, array $link_data) {
    $links = [];
    $weight = 0;
    foreach ($link_data ?: [] as $link) {
      $id = $entity_id . '.local_tasks.' . $cloud_context . '.' . $link['key'];
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $link['title'];
      $links[$id]['route_name'] = $link['route'];
      $links[$id]['menu_name'] = 'main';
      $links[$id]['parent'] = 'aws_cloud.menu.cloud_context:' . $entity_id . '.local_tasks.' . $cloud_context;
      $links[$id]['route_parameters'] = ['cloud_context' => $cloud_context];
      $links[$id]['weight'] = $weight++;
    }
    return $links;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $links = [];
    $weight = 0;

    // Generate a sort order list of Entity types (aws_cloud_<resource_name>).
    static $entity_types = [
      'aws_cloud_instance',
      'aws_cloud_image',
      'aws_cloud_security_group',
      'aws_cloud_elastic_ip',
      'aws_cloud_network_interface',
      'aws_cloud_key_pair',
      'aws_cloud_volume',
      'aws_cloud_snapshot',
      'aws_cloud_vpc',
      'aws_cloud_vpc_peering_connection',
      'aws_cloud_subnet',
    ];

    // Get all AWS cloud entities.
    $entities = $this->cloudConfigPluginManager->loadConfigEntities('aws_cloud');

    if (!empty($entities)) {
      // Add AWS Resources menu.
      $id = "aws_cloud.local_tasks.instance.all";
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $this->t('@title', ['@title' => "AWS resources"]);
      $links[$id]['route_name'] = "view.aws_cloud_instance.all";
      $links[$id]['menu_name'] = 'cloud.service_providers.menu.all';
      $links[$id]['parent'] = 'cloud.menu.cloud_links:cloud.service_providers.menu.all';
      $links[$id]['weight'] = $weight++;

      // Add dropdown menu for AWS cloud.
      $title = "AWS";
      $id = "aws_cloud.service_providers.menu";
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $this->t('@title', ['@title' => $title]);
      $links[$id]['route_name'] = "view.cloud_config.list";
      $links[$id]['menu_name'] = 'aws_cloud.service_providers.menu';
      $links[$id]['parent'] = 'cloud.service_providers.menu';
      $links[$id]['weight'] = $weight++;
      $links[$id]['expanded'] = TRUE;
    }

    foreach ($entities ?: [] as $entity) {
      // Add dropdown menus for cloud service provider from cloud_context.
      /* @var \Drupal\cloud\Entity\CloudConfig $entity */
      $id = "{$entity->id()}.local_tasks.{$entity->getCloudContext()}";
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $entity->label();
      $links[$id]['route_name'] = 'view.aws_cloud_instance.list';
      $links[$id]['base_route'] = 'aws_cloud.service_providers.menu';
      $links[$id]['parent'] = 'aws_cloud.menu.cloud_context:aws_cloud.service_providers.menu';
      $links[$id]['expanded'] = TRUE;
      $links[$id]['route_parameters'] = ['cloud_context' => $entity->getCloudContext()];
      $links[$id]['weight'] = $weight++;

      // Add sub menu items for <Resource Name>.
      $data = [];
      foreach ($entity_types ?: [] as $entity_type) {
        $entity_definition = $this->entityTypeManager->getDefinition($entity_type);
        if ($entity_definition !== NULL && $entity_definition->id() === $entity_type) {
          $title = preg_replace('/AWS Cloud (.*)/', '${1}s', (string) $entity_definition->getLabel());
          $data[] = [
            'key' => $entity_definition->id(),
            'title' => $this->t('@title', ['@title' => $title]),
            'route' => "view.{$entity_definition->id()}.list",
          ];
        }
      }

      // Add Instance Type Prices.
      $data[] = [
        'key' => 'instance_type_price',
        'title' => $this->t('Instance Type Prices'),
        'route' => 'aws_cloud.instance_type_prices',
      ];

      $links += $this->addChildLink($base_plugin_definition, $entity->getCloudContext(), $entity->id(), $data);
    }

    // Add dropdown menus for cloud design from cloud_context.
    $weight = 0;
    foreach ($entities ?: [] as $entity) {
      // Add dropdown menus for server template for each aws cloud_context.
      $id = "server.{$entity->id()}.design.local_tasks.{$entity->getCloudContext()}";
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $entity->label();
      $links[$id]['parent'] = 'cloud.menu.design_links:cloud_server.template';
      $links[$id]['route_name'] = 'entity.cloud_server_template.collection';
      $links[$id]['route_parameters'] = ['cloud_context' => $entity->getCloudContext()];
      $links[$id]['weight'] = $weight++;

      // Add dropdown menus for cloud budget for each aws cloud_context.
      if ($this->moduleHandler->moduleExists('cloud_budget')) {
        $id = "credit.{$entity->id()}.design.local_tasks.{$entity->getCloudContext()}";
        $links[$id] = $base_plugin_definition;
        $links[$id]['title'] = $entity->label();
        $links[$id]['parent'] = 'cloud.menu.design_links:cloud_budget.template';
        $links[$id]['route_name'] = 'view.cloud_credit.list';
        $links[$id]['route_parameters'] = ['cloud_context' => $entity->getCloudContext()];
        $links[$id]['weight'] = $weight++;
      }
    }
    return $links;
  }

}
