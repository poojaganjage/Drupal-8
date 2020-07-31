<?php

namespace Drupal\terraform\Plugin\Derivative;

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
 * @see \Drupal\terraform\Plugin\Derivative\TerraformMenuLinks
 */
class TerraformMenuLinks extends DeriverBase implements ContainerDeriverInterface {

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
   * Constructs new TerraformMenuLinks.
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
    // Get all Terraform entities.
    $entities = $this->cloudConfigPluginManager->loadConfigEntities('terraform');
    $links = [];
    $weight = 300;
    $entity_types = [
      'terraform_workspace',
    ];

    if (!empty($entities)) {
      // Add Terraform Resources menu.
      $id = "terraform.local_tasks.instance.all";
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $this->t('@title', ['@title' => "Terraform resources"]);
      $links[$id]['route_name'] = "view.terraform_workspace.all";
      $links[$id]['menu_name'] = 'cloud.service_providers.menu.all';
      $links[$id]['parent'] = 'cloud.menu.cloud_links:cloud.service_providers.menu.all';
      $links[$id]['weight'] = $weight++;

      // Add dropdown menu for Terraform.
      $title = "Terraform";
      $id = "terraform.service_providers.menu";
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $this->t('@title', ['@title' => $title]);
      $links[$id]['route_name'] = "view.cloud_config.list";
      $links[$id]['menu_name'] = 'terraform.service_providers.menu';
      $links[$id]['parent'] = 'cloud.service_providers.menu';
      $links[$id]['weight'] = $weight++;
      $links[$id]['expanded'] = TRUE;
    }

    foreach ($entities ?: [] as $entity) {
      $cloud_context = $entity->getCloudContext();
      $entity_id = $entity->id();
      $entity_label = $entity->label();
      $base_id = "$entity_id.local_tasks.$cloud_context";

      // Add menu items for terraform cluster.
      $menu_data = [];
      $menu_data[$base_id] = [
        'title' => $this->t('@entity_label', ['@entity_label' => $entity_label]),
        'route_name' => 'view.terraform_workspace.list',
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
      $links[$id]['base_route'] = 'terraform.service_providers.menu';
      $links[$id]['parent'] = 'terraform.menu.cloud_context:terraform.service_providers.menu';
      $links[$id]['expanded'] = TRUE;
      $links[$id]['route_parameters'] = [
        'cloud_context' => $cloud_context,
      ];

      $links[$id] = $link_data + $links[$id];

      // Add child items.
      $this->addChildItems(
        $links,
        $entity_types,
        $base_plugin_definition,
        $cloud_context,
        $id
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
      $title = preg_replace('/Terraform (.*)/', '${1}', $label);
      $id = "$parent_link_id.{$entity_definition->id()}";

      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $this->t('@title', ['@title' => $title]);
      $links[$id]['route_name'] = "view.{$entity_definition->id()}.list";
      $links[$id]['menu_name'] = 'main';
      $links[$id]['parent'] = "terraform.menu.cloud_context:$parent_link_id";
      $links[$id]['route_parameters'] = ['cloud_context' => $cloud_context] + $extra_route_parameters;
      $links[$id]['weight'] = $weight++;
    }
  }

}
