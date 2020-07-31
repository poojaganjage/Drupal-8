<?php

namespace Drupal\Tests\openstack\Traits;

use Drupal\Component\Utility\Random;
use Drupal\Tests\aws_cloud\Traits\AwsCloudTestFormDataTrait;

/**
 * The trait creating form data for openstack testing.
 */
trait OpenStackTestFormDataTrait {

  use AwsCloudTestFormDataTrait;

  /**
   * OPENSTACK_SECURITY_GROUP_REPEAT_COUNT.
   *
   * @var int
   */
  public static $openStackSecurityGroupRepeatCount = 2;

  /**
   * OPENSTACK_SECURITY_GROUP_RULES_REPEAT_COUNT.
   *
   * @var int
   */
  public static $openStackSecurityGroupRulesRepeatCount = 10;

  /**
   * OPENSTACK_SECURITY_GROUP_RULES_INBOUND.
   *
   * @var int
   */
  public static $openStackSecurityGroupRulesInbound = 0;

  /**
   * OPENSTACK_SECURITY_GROUP_RULES_OUTBOUND.
   *
   * @var int
   */
  public static $openStackSecurityGroupRulesOutbound = 1;

  /**
   * OPENSTACK_SECURITY_GROUP_RULES_MIX.
   *
   * @var int
   */
  public static $openStackSecurityGroupRulesMix = 2;

  /**
   * Create test data for cloud service provider (CloudConfig).
   *
   * @param int $max_count
   *   The max repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createOpenStackCloudConfigTestFormData($max_count = 1): array {
    $this->random = new Random();
    $cloud_context = $this->random->name(8, TRUE);

    // Input Fields.
    $data = [];
    for ($i = 0, $num = 1; $i < $max_count; $i++, $num++) {

      $data[] = [
        'name[0][value]'               => sprintf('config-entity-#%d-%s - %s', $num, $cloud_context, date('Y/m/d H:i:s')),
        'field_api_endpoint[0][value]' => 'http://openstack.endpoint:8788',
        'field_os_region[0][value]'    => 'RegionOne',
        'field_access_key[0][value]'   => $this->random->name(40, TRUE),
        'field_secret_key[0][value]'   => $this->random->name(20, TRUE),
        'field_account_id[0][value]'   => '',
      ];
    }

    return $data;
  }

  /**
   * Create instance test data.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   test data array.
   */
  protected function createOpenStackInstanceTestFormData($repeat_count = 1): array {
    $data = [];

    for ($i = 0, $num = 1; $i < $repeat_count; $i++, $num++) {

      $instance_name = sprintf('instance-entity #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(8, TRUE));

      // Input Fields.
      $data[$i] = [
        'name'                => $instance_name,
        'image_id'            => "ami-{$this->getRandomId()}",
        'min_count'           => $num,
        'max_count'           => $num * 2,
        'key_pair_name'       => "key_pair-${num}-{$this->random->name(8, TRUE)}",
        'is_monitoring'       => 0,
        'availability_zone'   => 'RegionOne',
        'security_groups[]'   => "security_group-$num-{$this->random->name(8, TRUE)}",
        'instance_type'       => 'm1.nano',
        'kernel_id'           => "aki-{$this->getRandomId()}",
        'ramdisk_id'          => "ari-{$this->getRandomId()}",
        'user_data'           => "User Data #${num}: {$this->random->name(64, TRUE)}",
        'tags[0][item_key]'   => 'Name',
        'tags[0][item_value]' => $instance_name,
      ];
    }

    return $data;
  }

  /**
   * Create cloud server template test data.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createOpenStackServerTemplateTestFormData($repeat_count = 1): array {
    $data = [];
    $random = $this->random;

    for ($i = 0, $num = 1, $instance_family = 3; $i < $repeat_count; $i++, $num++, $instance_family++) {

      // Input Fields.
      $data[] = [
        'cloud_context[0][value]' => $this->cloudContext,
        'name[0][value]' => "LaunchTemplate-{$random->name(16, TRUE)}",
        'field_description[0][value]' => "#$num: " . date('Y/m/d H:i:s - D M j G:i:s T Y')
        . ' - SimpleTest Launch Template Description - '
        . $random->name(32, TRUE),
        'field_test_only[value]' => '1',
        'field_os_availability_zone' => 'RegionOne',
        'field_monitoring[value]' => '1',
        'field_openstack_image_id' => "ami-{$this->getRandomId()}",
        'field_min_count[0][value]' => 1,
        'field_max_count[0][value]' => 1,
        'field_kernel_id[0][value]' => "aki-{$this->getRandomId()}",
        'field_ram[0][value]' => "ari-{$this->getRandomId()}",
        'field_openstack_security_group' => 1,
        'field_openstack_ssh_key' => 1,
        'field_tags[0][item_key]' => "key_{$random->name(8, TRUE)}",
        'field_tags[0][item_value]' => "value_{$random->name(8, TRUE)}",
      ];
    }
    return $data;
  }

}
