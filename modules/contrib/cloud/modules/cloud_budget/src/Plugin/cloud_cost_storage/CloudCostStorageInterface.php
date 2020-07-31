<?php

namespace Drupal\cloud_budget\Plugin\cloud_cost_storage;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for cloud cost storage plugins.
 *
 * @see \Drupal\cloud_budget\Annotation\CloudCostCalculator
 * @see \Drupal\cloud_budget\Plugin\cloud_cost_storage\CloudCostStorageBase
 * @see \Drupal\cloud_budget\Plugin\cloud_cost_storage\CloudCostStorageManager
 * @see plugin_api
 */
interface CloudCostStorageInterface extends PluginInspectionInterface {

  /**
   * Insert content into entity.
   *
   * @param string $entity_type_id
   *   The entity type id.
   * @param string $cloud_context
   *   The cloud context.
   * @param string $payer
   *   The payer of the cost.
   * @param float $cost
   *   The contents to be put into entity.
   * @param float $resource
   *   The resource to be put into entity.
   * @param float $time_range
   *   The time range user used resources.
   */
  public function save($entity_type_id, $cloud_context, $payer, $cost, $resource, $time_range);

  /**
   * Delete content from entity.
   *
   * @param string $entity_type_id
   *   The entity type id.
   * @param string $payer
   *   The payer of the cost.
   */
  public function deleteAll($entity_type_id, $payer);

  /**
   * Delete content from entity.
   *
   * @param string $entity_type_id
   *   The entity type id.
   * @param int $from_time
   *   The unix timestamp of the starting time.
   * @param int $to_time
   *   The unix timestamp of the ending time.
   */
  public function deleteTimeBased($entity_type_id, $from_time, $to_time);

  /**
   * Extract data based on time range.
   *
   * @param string $entity_type_id
   *   The entity type id.
   * @param string $cloud_context
   *   The cloud context.
   * @param string $payer
   *   The payer of the cost.
   * @param int $from_time
   *   The unix timestamp of the starting time.
   * @param int $to_time
   *   The unix timestamp of the ending time.
   */
  public function extract($entity_type_id, $cloud_context, $payer, $from_time, $to_time);

  /**
   * Update cost storage.
   */
  public function updateCostStorageEntity();

  /**
   * Update resource storage.
   */
  public function updateResourceStorageEntity();

  /**
   * Delete resource storage.
   */
  public function deleteResourceStorageEntity();

}
