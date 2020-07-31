<?php

namespace Drupal\cloud_budget\Plugin\cloud_budget;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for cloud cost calculator plugins.
 *
 * @see \Drupal\cloud_budget\Annotation\CloudCostCalculator
 * @see \Drupal\cloud_budget\Plugin\cloud_budget\CloudCostCalculatorBase
 * @see \Drupal\cloud_budget\Plugin\cloud_budget\CloudCostCalculatorManager
 * @see plugin_api
 */
interface CloudCostCalculatorInterface extends PluginInspectionInterface {

  /**
   * Calculate cost.
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
   * @return int
   *   The cost.
   */
  public function calculate($uid, $cloud_context, $from_time, $to_time);

}
