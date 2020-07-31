<?php

namespace Drupal\Tests\cloud\Traits;

use Drupal\Component\Utility\Random;

/**
 * The trait creating form data for terraform testing.
 */
trait CloudConfigTestFormDataTrait {

  /**
   * Create random cloud service provider (CloudConfig) data.
   *
   * @return array
   *   Random cloud service providers (CloudConfig).
   *
   * @throws \Exception
   */
  protected function createCloudConfigRandomTestFormData(): array {
    $cloud_configs = [];
    $random = new Random();
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $cloud_configs[] = [
        'CloudContext' => "cloud_context-{$this->random->name(8)}",
        'Name' => sprintf('config-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $random->name(4, TRUE)),
      ];
    }

    return $cloud_configs;
  }

}
