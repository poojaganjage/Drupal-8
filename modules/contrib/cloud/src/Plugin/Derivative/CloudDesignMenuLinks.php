<?php

namespace Drupal\cloud\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides plugin definitions for custom local menu.
 *
 * @see \Drupal\cloud\Plugin\Derivative\CloudLocalTasks
 */
class CloudDesignMenuLinks extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs new CloudLocalTasks.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ModuleHandlerInterface $module_handler) {
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $links = [];
    $weight = 0;

    // Add dropdown menu for server template.
    $id = "cloud_server.template";
    $links[$id] = $base_plugin_definition;
    $links[$id]['title'] = t('Launch Templates');
    $links[$id]['parent'] = 'cloud.design.menu';
    $links[$id]['expanded'] = TRUE;
    $links[$id]['route_name'] = "view.cloud_config.list";
    $links[$id]['weight'] = $weight++;

    // Add dropdown menu for projects.
    $id = "cloud_project.template";
    $links[$id] = $base_plugin_definition;
    $links[$id]['title'] = t('Projects');
    $links[$id]['parent'] = 'cloud.design.menu';
    $links[$id]['expanded'] = TRUE;
    $links[$id]['route_name'] = 'view.cloud_config.list';
    $links[$id]['weight'] = $weight++;

    // Add dropdown menu for cloud credits.
    if ($this->moduleHandler->moduleExists('cloud_budget')) {
      $id = "cloud_budget.template";
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = t('Cloud Credits');
      $links[$id]['parent'] = 'cloud.design.menu';
      $links[$id]['expanded'] = TRUE;
      $links[$id]['route_name'] = "view.cloud_config.list";
      $links[$id]['weight'] = $weight++;
    }
    return $links;
  }

}
