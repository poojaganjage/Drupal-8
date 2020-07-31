<?php

namespace Drupal\Tests\aws_cloud\Functional\Ec2;

use Drupal\aws_cloud\Entity\Ec2\Instance;
use Drupal\Tests\aws_cloud\Functional\AwsCloudTestBase;
use Drupal\Tests\cloud\Functional\Utils;

/**
 * Tests AWS Cloud Instance by focusing on the "Bulk" operations only.
 *
 * @group AWS Cloud
 */
class InstanceBulkTest extends AwsCloudTestBase {

  /**
   * Create three Instances for a test case.
   */
  public const AWS_CLOUD_INSTANCE_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'add aws cloud instance',
      'list aws cloud instance',
      'edit own aws cloud instance',
      'delete own aws cloud instance',
      'view own aws cloud instance',
      'edit any aws cloud instance',

      'list cloud server template',
      'view own published cloud server templates',
      'launch cloud server template',

      'add aws cloud image',
      'list aws cloud image',
      'view any aws cloud image',
      'edit any aws cloud image',
      'delete any aws cloud image',

      'administer aws_cloud',
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
    $regions = ['us-west-1', 'us-west-2'];
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
    $regions = ['us-west-1', 'us-west-2'];

    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++, $num++) {
      // Create instances.
      $instances = $this->createInstancesRandomTestFormData();
      $entities = [];
      foreach ($instances ?: [] as $instance) {
        $entities[] = $this->createInstanceTestEntity(Instance::class, $num, $regions, NULL, $instance['Name'], $instance['InstanceId']);
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
    $regions = ['us-west-1', 'us-west-2'];

    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++, $num++) {
      // Create instances.
      $instances = $this->createInstancesRandomTestFormData();
      $entities = [];
      foreach ($instances ?: [] as $instance) {
        $entities[] = $this->createInstanceTestEntity(Instance::class, $num, $regions, NULL, $instance['Name'], $instance['InstanceId'], 'stopped');
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
    $regions = ['us-west-1', 'us-west-2'];

    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++, $num++) {
      // Create instances.
      $instances = $this->createInstancesRandomTestFormData();
      $entities = [];
      foreach ($instances ?: [] as $instance) {
        $entities[] = $this->createInstanceTestEntity(Instance::class, $num, $regions, NULL, $instance['Name'], $instance['InstanceId']);
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
    $regions = ['us-west-1', 'us-west-2'];

    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++, $num++) {
      // Create instances.
      $instances = $this->createInstancesRandomTestFormData();
      $entities = [];
      foreach ($instances ?: [] as $instance) {
        $entities[] = $this->createInstanceTestEntity(Instance::class, $num, $regions, NULL, $instance['Name'], $instance['InstanceId']);
      }

      $this->runTestEntityBulk('instance', $entities, 'reboot', 'rebooted');
    }
  }

}
