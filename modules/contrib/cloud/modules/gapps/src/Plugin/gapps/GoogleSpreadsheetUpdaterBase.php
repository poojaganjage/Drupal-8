<?php

namespace Drupal\gapps\Plugin\gapps;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\gapps\Service\GoogleSpreadsheetService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a base google spreadsheet updater implementation.
 *
 * @see \Drupal\gapps\Annotation\GoogleSpreadsheetUpdater
 * @see \Drupal\gapps\Plugin\gapps\GoogleSpreadsheetUpdaterInterface
 * @see \Drupal\gapps\Plugin\gapps\GoogleSpreadsheetUpdaterManager
 * @see plugin_api
 */
abstract class GoogleSpreadsheetUpdaterBase extends PluginBase implements GoogleSpreadsheetUpdaterInterface, ContainerFactoryPluginInterface {

  /**
   * The Entity type manager.
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
   * The google spreadsheet service.
   *
   * @var \Drupal\gapps\Service\GoogleSpreadsheetService
   */
  protected $googleSpreadsheetService;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * GoogleSpreadsheetUpdaterBase constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity type manager.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   * @param \Drupal\gapps\Service\GoogleSpreadsheetService $google_spreadsheet_service
   *   The google spreadsheet service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
    GoogleSpreadsheetService $google_spreadsheet_service,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
    $this->googleSpreadsheetService = $google_spreadsheet_service;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.cloud_config_plugin'),
      $container->get('gapps.google_spreadsheet'),
      $container->get('config.factory')
    );
  }

}
