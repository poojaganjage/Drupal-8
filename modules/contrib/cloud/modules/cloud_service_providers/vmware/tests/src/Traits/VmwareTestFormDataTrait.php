<?php

namespace Drupal\Tests\vmware\Traits;

use Drupal\Component\Utility\Random;

/**
 * The trait creating form data for vmware testing.
 */
trait VmwareTestFormDataTrait {

  /**
   * Create test data for cloud service provider (CloudConfig).
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createCloudConfigTestFormData($repeat_count): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0, $num = 1; $i < $repeat_count; $i++, $num++) {

      $data[] = [
        'name[0][value]'                   => sprintf('config-entity-#%d-%s - %s', $num, $random->name(8, TRUE), date('Y/m/d H:i:s')),
        'cloud_context'                    => strtolower($random->name(16, TRUE)),
        'field_vcenter_url[0][value]'      => 'http://example.com/',
        'field_vcenter_username[0][value]' => $random->name(16, TRUE),
        'field_vcenter_password[0][value]' => $random->name(16, TRUE),
      ];
    }

    return $data;
  }

}
