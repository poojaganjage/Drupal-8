<?php

namespace Drupal\Tests\cloud_budget\Traits;

use Drupal\Component\Utility\Random;

/**
 * The trait creating form data for cloud budget testing.
 */
trait CloudBudgetTestFormDataTrait {

  /**
   * Create test data for cloud credit.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createCloudCreditTestFormData($repeat_count): array {

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $user = $this->drupalCreateUser();
      $data[] = [
        'user[0][target_id]' => "{$user->getDisplayName()} ({$user->id()})",
        'amount[0][value]' => 2000,
      ];
    }

    return $data;
  }

  /**
   * Create random cloud credits.
   *
   * @return array
   *   Random cloud credits array.
   */
  protected function createCloudCreditsRandomTestFormData(): array {
    $count = random_int(1, 10);
    $cloud_credits = [];
    for ($i = 0; $i < $count; $i++) {
      $user = $this->drupalCreateUser();
      $cloud_credits[] = [
        'cloud_context' => $this->cloudContext,
        'user' => $user,
        'amount' => random_int(1000, 2000),
      ];
    }

    return $cloud_credits;
  }

  /**
   * Create random cloud cost storage.
   *
   * @param int $count
   *   Repeat count.
   *
   * @return array
   *   Random cloud cost storage array.
   */
  protected function createCloudCostStorageRandomTestFormData($count): array {
    $cloud_cost_storage = [];
    $time = time();
    $random = new Random();

    for ($i = 0; $i < $count; $i++) {
      $payer = 'payer-' . strtolower($random->name(8, TRUE));
      $cloud_cost_storage[] = [
        'cloud_context' => $this->cloudContext,
        'payer' => $payer,
        'cost' => random_int(100, 100000) / 100.0,
        'refreshed' => $time + 60 * $i,
      ];
    }

    return $cloud_cost_storage;
  }

}
