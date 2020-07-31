<?php

namespace Drupal\cloud\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides plugin definitions for custom local menu.
 *
 * @see \Drupal\cloud\Plugin\Derivative\CloudLocalTasks
 */
class CloudMenuLinks extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs new CloudLocalTasks.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    // Get cloud config type.
    $cloud_config_type = $this->entityTypeManager
      ->getStorage('cloud_config_type')
      ->getQuery()
      ->execute();

    // Get cloud config.
    $cloud_config = $this->entityTypeManager
      ->getStorage('cloud_config')
      ->getQuery()
      ->execute();

    $cloud_service_provider = array_filter($cloud_config);
    $cloud_service_count = !empty($cloud_service_provider) ? count($cloud_service_provider) : '';

    $cloud_service_provider = array_filter($cloud_config);
    $cloud_service_count = !empty($cloud_service_provider) ? count($cloud_service_provider) : '';

    $links = [];

    if ($cloud_service_count <= 0) {
      $id = "cloud_service_provider.local_tasks";
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $this->t('Add Cloud Service Provider');
      $links[$id]['parent'] = 'cloud.service_providers.menu';
      $links[$id]['route_parameters'] = [];
      $links[$id]['weight'] = -110;
      $links[$id]['route_name'] = 'entity.cloud_config.add_page';
    }
    else {
      // Add dropdown menu for all.
      $title = "All";
      $id = "cloud.service_providers.menu.all";
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $this->t('@title', ['@title' => $title]);
      $links[$id]['route_name'] = "view.cloud_config.list";
      $links[$id]['menu_name'] = 'cloud.service_providers.menu.all';
      $links[$id]['parent'] = 'cloud.service_providers.menu';
      $links[$id]['weight'] = -100;
      $links[$id]['expanded'] = TRUE;

      // Add dropdown menu for cloud service provider.
      $title = "Cloud service providers";
      $id = "list.service_providers.menu";
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $this->t('@title', ['@title' => $title]);
      $links[$id]['route_name'] = "view.cloud_config.list";
      $links[$id]['menu_name'] = 'list.service_providers.menu';
      $links[$id]['parent'] = 'cloud.menu.cloud_links:cloud.service_providers.menu.all';
      $links[$id]['weight'] = -120;
    }
    return $links;
  }

}
