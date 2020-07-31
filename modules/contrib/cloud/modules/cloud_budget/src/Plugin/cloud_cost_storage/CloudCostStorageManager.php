<?php

namespace Drupal\cloud_budget\Plugin\cloud_cost_storage;

use Drupal\cloud\Plugin\cloud\CloudPluginManager;
use Drupal\cloud_budget\Annotation\CloudCostStorage;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides a cloud cost manager.
 *
 * @see \Drupal\cloud_budget\Annotation\CloudCostStorage
 * @see \Drupal\cloud_budget\Plugin\cloud_cost_storage\CloudCostStorageBase
 * @see \Drupal\cloud_budget\Plugin\cloud_cost_storage\CloudCostStorageInterface
 * @see plugin_api
 */
class CloudCostStorageManager extends CloudPluginManager {

  /**
   * Constructs a InPlaceEditorManager object.
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
    parent::__construct('Plugin/cloud_cost_storage', $namespaces, $module_handler, CloudCostStorageInterface::class, CloudCostStorage::class);
    $this->alterInfo('cloud_cost_storage');
    $this->setCacheBackend($cache_backend, 'cloud_budget:cloud_cost_storage');
  }

  /**
   * Update all target storage.
   */
  public function updateCostStorageAll(): void {
    foreach ($this->getDefinitions() ?: [] as $id => $definition) {
      $plugin = $this->createInstance($id);
      $plugin->updateCostStorageEntity();
    }
  }

}
