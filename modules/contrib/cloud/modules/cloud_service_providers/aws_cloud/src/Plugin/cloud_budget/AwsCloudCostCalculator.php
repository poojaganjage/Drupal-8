<?php

namespace Drupal\aws_cloud\Plugin\cloud_budget;

use Drupal\cloud_budget\Plugin\cloud_budget\CloudCostCalculatorBase;

/**
 * Defines the cost calculator.
 *
 * @CloudCostCalculator(
 *   id = "aws_cloud_cost_calculator"
 * )
 */
class AwsCloudCostCalculator extends CloudCostCalculatorBase {

  /**
   * {@inheritdoc}
   */
  public function calculate($uid, $cloud_context, $from_time, $to_time) {

    // Calculate cost of instances.
    $instances = $this->entityTypeManager
      ->getStorage('aws_cloud_instance')
      ->loadByProperties([
        'cloud_context' => $cloud_context,
        'uid' => $uid,
        'instance_state' => 'running',
      ]);

    if (empty($instances)) {
      return 0;
    }

    $instance_types = aws_cloud_get_instance_types($cloud_context);

    // In hours.
    $period = (float) ($to_time - $from_time) / 1000 / 60 / 60;

    $cost = 0;
    foreach ($instances ?: [] as $instance) {
      $instance_type_info = $instance_types[$instance->getInstanceType()];
      $parts = explode(':', $instance_type_info);
      $hourly_rate = $parts[4];
      $cost += $hourly_rate * $period;
    }

    return $cost;
  }

}
