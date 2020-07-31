<?php

namespace Drupal\cloud_budget\Plugin\cloud_budget;

use Drupal\cloud\Plugin\cloud\CloudPluginManager;
use Drupal\cloud_budget\Annotation\CloudCostCalculator;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides a cloud cost calculator manager.
 *
 * @see \Drupal\cloud_budget\Annotation\CloudCostCalculator
 * @see \Drupal\cloud_budget\Plugin\cloud_budget\CloudCostCalculatorBase
 * @see \Drupal\cloud_budget\Plugin\cloud_budget\CloudCostCalculatorInterface
 * @see plugin_api
 */
class CloudCostCalculatorManager extends CloudPluginManager {

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
    parent::__construct('Plugin/cloud_budget', $namespaces, $module_handler, CloudCostCalculatorInterface::class, CloudCostCalculator::class);
    $this->alterInfo('cloud_cost_calculator');
    $this->setCacheBackend($cache_backend, 'cloud_budget:cloud_cost_calculator');
  }

  /**
   * Call all calculators.
   *
   * @param int $uid
   *   The user id.
   * @param string $cloud_context
   *   The cloud context.
   * @param int $from_time
   *   The unix timestamp of the starting time.
   * @param int $to_time
   *   The unix timestamp of the ending time.
   *
   * @return array
   *   The results of all calculators.
   */
  public function calculateAll($uid, $cloud_context, $from_time, $to_time) {
    $results = [];
    foreach ($this->getDefinitions() ?: [] as $id => $definition) {
      $plugin = $this->createInstance($id);
      $results[] = $plugin->calculate($uid, $cloud_context, $from_time, $to_time);
    }
    return $results;
  }

}
