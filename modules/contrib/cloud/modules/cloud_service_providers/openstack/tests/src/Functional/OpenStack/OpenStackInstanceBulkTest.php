<?php

namespace Drupal\Tests\openstack\Functional\OpenStack;

use Drupal\openstack\Entity\OpenStackInstance;
use Drupal\Tests\openstack\Functional\OpenStackTestBase;
use Drupal\Tests\cloud\Functional\Utils;

/**
 * Tests OpenStack Instance by focusing on the "Bulk" operations only.
 *
 * @group OpenStack
 */
class OpenStackInstanceBulkTest extends OpenStackTestBase {

  /**
   * Create three Instances for a test case.
   */
  public const OPENSTACK_INSTANCE_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'add openstack instance',
      'list openstack instances',
      'edit own openstack instance',
      'delete own openstack instance',
      'view own openstack instance',
      'edit any openstack instance',

      'list cloud server template',
      'view own published cloud server templates',
      'launch cloud server template',

      'add openstack image',
      'list openstack images',
      'view any openstack image',
      'edit any openstack image',
      'delete any openstack image',

      'administer openstack',
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  protected function getMockDataTemplateVars(): array {
    $public_ip = Utils::getRandomPublicIp();
    $private_ip = Utils::getRandomPrivateIp();
    $regions = ['RegionOne'];
    $region = $regions[array_rand($regions)];

    return [
      // 12 digits.
      'account_id' => mt_rand(100000000000, 999999999999),
      'reservation_id' => 'r-' . $this->getRandomId(),
      'group_name' => $this->random->name(8, TRUE),
      'host_id' => $this->random->name(8, TRUE),
      'affinity' => $this->random->name(8, TRUE),
      'launch_time' => date('c'),
      'security_group_id' => 'sg-' . $this->getRandomId(),
      'security_group_name' => $this->random->name(10, TRUE),
      'public_dns_name' => Utils::getPublicDns($region, $public_ip),
      'public_ip_address' => $public_ip,
      'private_dns_name' => Utils::getPrivateDns($region, $private_ip),
      'private_ip_address' => $private_ip,
      'vpc_id' => 'vpc-' . $this->getRandomId(),
      'subnet_id' => 'subnet-' . $this->getRandomId(),
      'image_id' => 'ami-' . $this->getRandomId(),
      'reason' => $this->random->string(16, TRUE),
      'instance_id' => 'i-' . $this->getRandomId(),
      'state' => 'running',
    ];
  }

  /**
   * Tests deleting instances with bulk operation.
   *
   * @throws \Exception
   */
  public function testDeleteInstanceBulk(): void {
    $regions = ['RegionOne'];

    for ($i = 0, $num = 1; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++, $num++) {
      // Create instances.
      $instances = $this->createInstancesRandomTestFormData();
      $entities = [];
      foreach ($instances ?: [] as $instance) {
        $entities[] = $this->createInstanceTestEntity(OpenStackInstance::class, $num, $regions, NULL, $instance['Name'], $instance['InstanceId']);
      }

      $this->runTestEntityBulk('instance', $entities);
    }
  }

  /**
   * Tests starting instances with bulk operation.
   *
   * @throws \Exception
   */
  public function testStartInstanceBulk() {
    $regions = ['RegionOne'];

    for ($i = 0, $num = 1; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++, $num++) {
      // Create instances.
      $instances = $this->createInstancesRandomTestFormData();
      $entities = [];
      foreach ($instances ?: [] as $instance) {
        $entities[] = $this->createInstanceTestEntity(OpenStackInstance::class, $num, $regions, NULL, $instance['Name'], $instance['InstanceId'], 'stopped');
      }

      $this->runTestEntityBulk('instance', $entities, 'start', 'started');
    }
  }

  /**
   * Tests stopping instances with bulk operation.
   *
   * @throws \Exception
   */
  public function testStopInstanceBulk(): void {
    $regions = ['RegionOne'];

    for ($i = 0, $num = 1; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++, $num++) {
      // Create instances.
      $instances = $this->createInstancesRandomTestFormData();
      $entities = [];
      foreach ($instances ?: [] as $instance) {
        $entities[] = $this->createInstanceTestEntity(OpenStackInstance::class, $num, $regions, NULL, $instance['Name'], $instance['InstanceId']);
      }

      $this->runTestEntityBulk('instance', $entities, 'stop', 'stopped');
    }
  }

  /**
   * Tests rebooting instances with bulk operation.
   *
   * @throws \Exception
   */
  public function testRebootInstanceBulk(): void {
    $regions = ['RegionOne'];

    for ($i = 0, $num = 1; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++, $num++) {
      // Create instances.
      $instances = $this->createInstancesRandomTestFormData();
      $entities = [];
      foreach ($instances ?: [] as $instance) {
        $entities[] = $this->createInstanceTestEntity(OpenStackInstance::class, $num, $regions, NULL, $instance['Name'], $instance['InstanceId']);
      }

      $this->runTestEntityBulk('instance', $entities, 'reboot', 'rebooted');
    }
  }

}
