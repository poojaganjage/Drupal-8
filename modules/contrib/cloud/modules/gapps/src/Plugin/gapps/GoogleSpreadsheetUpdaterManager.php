<?php

namespace Drupal\gapps\Plugin\gapps;

use Drupal\cloud\Plugin\cloud\CloudPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\gapps\Annotation\GoogleSpreadsheetUpdater;

/**
 * Provides a google spreadsheet updater manager.
 *
 * @see \Drupal\gapps\Annotation\GoogleSpreadsheetUpdater
 * @see \Drupal\gapps\Plugin\gapps\GoogleSpreadsheetUpdaterBase
 * @see \Drupal\gapps\Plugin\gapps\GoogleSpreadsheetUpdaterInterface
 * @see plugin_api
 */
class GoogleSpreadsheetUpdaterManager extends CloudPluginManager {

  /**
   * Constructs a GoogleSpreadsheetUpdaterManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/gapps', $namespaces, $module_handler, GoogleSpreadsheetUpdaterInterface::class, GoogleSpreadsheetUpdater::class);
    $this->alterInfo('google_spreadsheet_updater');
    $this->setCacheBackend($cache_backend, 'gapps:google_spreadsheet_updater');
  }

  /**
   * Delete google spreadsheets.
   *
   * @return array
   *   The cloud configs changed.
   */
  public function deleteAllSpreadsheets() {
    $cloud_configs = [];
    foreach ($this->getDefinitions() ?: [] as $id => $definition) {
      $plugin = $this->createInstance($id);
      $cloud_configs = array_merge($cloud_configs, $plugin->deleteSpreadsheets());
    }
    return $cloud_configs;
  }

}
