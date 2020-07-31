<?php

namespace Drupal\Tests\aws_cloud\Unit\Service\Ec2;

use Drupal\aws_cloud\Service\Ec2\Ec2Service;

/**
 * Mock class for Ec2Service.
 */
class Ec2ServiceMock extends Ec2Service {

  /**
   * Availability zones for test.
   *
   * @var array
   */
  private $zones;

  /**
   * Set availability zones for test.
   *
   * @param array $zones
   *   Zones array.
   */
  public function setAvailabilityZonesForTest(array $zones) {
    $this->zones = $zones;
  }

  /**
   * {@inheritdoc}
   */
  public function describeAvailabilityZones(array $params = []) {
    return $this->zones;
  }

}
