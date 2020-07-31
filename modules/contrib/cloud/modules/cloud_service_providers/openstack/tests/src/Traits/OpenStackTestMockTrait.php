<?php

namespace Drupal\Tests\openstack\Traits;

use Drupal\Tests\aws_cloud\Traits\AwsCloudTestMockTrait;

/**
 * The trait creating mock data for openstack testing.
 */
trait OpenStackTestMockTrait {

  // Most of functions depends on AwsCloudTestMockTrait.
  use AwsCloudTestMockTrait;

  /**
   * Update describe regions in mock data.
   *
   * @param array $regions
   *   Regions array.
   */
  protected function updateDescribeRegionsMockData(array $regions): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeRegions']['Regions'][] = $regions;
    $this->updateMockDataToConfig($mock_data);
  }

}
