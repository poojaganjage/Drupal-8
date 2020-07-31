<?php

namespace Drupal\k8s\Plugin\Derivative;

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
 * @see \Drupal\k8s\Plugin\Derivative\K8sMenuLinks
 */
class K8sMenuLinks extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManagerInterface;

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
   * Constructs new K8sMenuLinks.
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
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    // Get all K8s entities.
    $entities = $this->cloudConfigPluginManager->loadConfigEntities('k8s');
    $links = [];
    $weight = 100;
    $entity_types = [
      'k8s_node',
      'k8s_namespace',
      'k8s_pod',
      'k8s_deployment',
      'k8s_replica_set',
      'k8s_service',
      'k8s_cron_job',
      'k8s_job',
      'k8s_resource_quota',
      'k8s_limit_range',
      'k8s_secret',
      'k8s_config_map',
      'k8s_network_policy',
      'k8s_role',
      'k8s_role_binding',
      'k8s_cluster_role',
      'k8s_cluster_role_binding',
      'k8s_persistent_volume',
      'k8s_persistent_volume_claim',
      'k8s_storage_class',
      'k8s_stateful_set',
      'k8s_ingress',
      'k8s_daemon_set',
      'k8s_endpoint',
      'k8s_event',
      'k8s_api_service',
      'k8s_service_account',
      'k8s_priority_class',
    ];

    if (!empty($entities)) {

      // Add K8s Resources menu.
      $id = "k8s.local_tasks.k8s_node.all";
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $this->t('@title', ['@title' => "K8s resources"]);
      $links[$id]['route_name'] = "view.k8s_node.all";
      $links[$id]['menu_name'] = 'cloud.service_providers.menu.all';
      $links[$id]['parent'] = 'cloud.menu.cloud_links:cloud.service_providers.menu.all';
      $links[$id]['weight'] = $weight++;

      // Add dropdown menu for K8s.
      $title = "K8s";
      $id = "k8s.service_providers.menu";
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $this->t('@title', ['@title' => $title]);
      $links[$id]['route_name'] = "view.cloud_config.list";
      $links[$id]['menu_name'] = 'k8s.service_providers.menu';
      $links[$id]['parent'] = 'cloud.service_providers.menu';
      $links[$id]['weight'] = $weight++;
      $links[$id]['expanded'] = TRUE;
    }

    foreach ($entities ?: [] as $entity) {
      $cloud_context = $entity->getCloudContext();
      $entity_id = $entity->id();
      $entity_label = $entity->label();
      $base_id = "$entity_id.local_tasks.$cloud_context";

      // Add menu items for k8s cluster.
      $menu_data = [];
      $menu_data[$base_id] = [
        'title' => $this->t('@entity_label', ['@entity_label' => $entity_label]),
        'route_name' => 'view.k8s_node.list',
        'weight' => $weight++,
      ];

      $this->addMenuItems(
        $links,
        $base_plugin_definition,
        $cloud_context,
        $entity_types,
        $menu_data
      );
    }

    // Add dropdown menus for cloud design from cloud_context.
    $weight = 100;
    foreach ($entities ?: [] as $entity) {
      // Add dropdown menus for cloud budget for each k8s cloud_context.
      $id = "server.{$entity->id()}.design.local_tasks.{$entity->getCloudContext()}";
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $entity->label();
      $links[$id]['parent'] = 'cloud.menu.design_links:cloud_server.template';
      $links[$id]['route_name'] = 'entity.cloud_server_template.collection';
      $links[$id]['route_parameters'] = ['cloud_context' => $entity->getCloudContext()];
      $links[$id]['weight'] = $weight++;

      // Add dropdown menus for projects for each k8s cloud_context.
      $id = "project.{$entity->id()}.design.local_tasks.{$entity->getCloudContext()}";
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $entity->label();
      $links[$id]['parent'] = 'cloud.menu.design_links:cloud_project.template';
      $links[$id]['route_name'] = 'entity.cloud_project.collection';
      $links[$id]['route_parameters'] = ['cloud_context' => $entity->getCloudContext()];
      $links[$id]['weight'] = $weight++;

      // Add dropdown menus for cloud credit for each k8s cloud_context.
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

  /**
   * Add menu items to the links.
   *
   * @param array &$links
   *   Links.
   * @param array $base_plugin_definition
   *   An array of the base_plugin_definition.
   * @param string $cloud_context
   *   The cloud context.
   * @param array $entity_types
   *   The entity types.
   * @param array $menu_data
   *   The data of menu.
   */
  private function addMenuItems(
    array &$links,
    array $base_plugin_definition,
    $cloud_context,
    array $entity_types,
    array $menu_data) {

    foreach ($menu_data ?: [] as $id => $link_data) {
      $links[$id] = $base_plugin_definition;
      $links[$id]['base_route'] = 'k8s.service_providers.menu';
      $links[$id]['parent'] = 'k8s.menu.cloud_context:k8s.service_providers.menu';
      $links[$id]['expanded'] = TRUE;
      $links[$id]['route_parameters'] = [
        'cloud_context' => $cloud_context,
      ];

      $links[$id] = $link_data + $links[$id];

      // Get extra route parameters.
      $extra_route_parameters = [];
      if (!empty($namespace) && !empty($link_data['route_parameters']['k8s_namespace'])) {
        $namespace = $this->entityTypeManager
          ->getStorage('k8s_namespace')
          ->load($link_data['route_parameters']['k8s_namespace']);
        $extra_route_parameters['namespace'] = !empty($namespace) ?: $namespace->getName();
      }

      // Add child items.
      $this->addChildItems(
        $links,
        $entity_types,
        $base_plugin_definition,
        $cloud_context,
        $id,
        $extra_route_parameters
      );
    }
  }

  /**
   * Add child items to the links.
   *
   * @param array &$links
   *   The links.
   * @param array $entity_types
   *   The entity types.
   * @param array $base_plugin_definition
   *   An array of the base_plugin_definition.
   * @param string $cloud_context
   *   The cloud context.
   * @param string $parent_link_id
   *   The ID of the parent link.
   * @param array $extra_route_parameters
   *   The extra route parameters.
   */
  private function addChildItems(
    array &$links,
    array $entity_types,
    array $base_plugin_definition,
    $cloud_context,
    $parent_link_id,
    array $extra_route_parameters = []) {

    $weight = 0;
    foreach ($entity_types ?: [] as $entity_type) {
      $entity_definition = $this->entityTypeManager->getDefinition($entity_type);
      if ($entity_definition === NULL) {
        continue;
      }

      $label = (string) $entity_definition->getCollectionLabel();
      $title = preg_replace('/Kubernetes (.*)/', '${1}', $label);
      $id = "$parent_link_id.{$entity_definition->id()}";

      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $this->t('@title', ['@title' => $title]);
      $links[$id]['route_name'] = "view.{$entity_definition->id()}.list";
      $links[$id]['menu_name'] = 'main';
      $links[$id]['parent'] = "k8s.menu.cloud_context:$parent_link_id";
      $links[$id]['route_parameters'] = ['cloud_context' => $cloud_context] + $extra_route_parameters;
      $links[$id]['weight'] = $weight++;
    }
  }

  /**
   * Get k8s namespace entities.
   *
   * @param string $cloud_context
   *   The cloud context.
   *
   * @return array
   *   The k8s namespace entities.
   */
  private function getNamespaceEntities($cloud_context) {
    $entity_storage = $this->entityTypeManager->getStorage('k8s_namespace');
    $entity_ids = $entity_storage
      ->getQuery()
      ->condition('cloud_context', $cloud_context)
      ->execute();
    return $entity_storage->loadMultiple($entity_ids);
  }

}
