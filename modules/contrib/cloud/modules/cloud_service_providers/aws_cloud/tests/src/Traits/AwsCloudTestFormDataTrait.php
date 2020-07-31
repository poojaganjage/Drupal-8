<?php

namespace Drupal\Tests\aws_cloud\Traits;

use Drupal\Component\Utility\Random;
use Drupal\Tests\cloud\Functional\Utils;

/**
 * The trait creating form data for aws cloud testing.
 */
trait AwsCloudTestFormDataTrait {

  /**
   * AWS_CLOUD_CONFIG_REPEAT_COUNT.
   *
   * @var int
   */
  public static $awsCloudConfigRepeatCount = 1;

  /**
   * AWS_CLOUD_SECURITY_GROUP_REPEAT_COUNT.
   *
   * @var int
   */
  public static $awsCloudSecurityGroupRepeatCount = 2;

  /**
   * AWS_CLOUD_SECURITY_GROUP_RULES_REPEAT_COUNT.
   *
   * @var int
   */
  public static $awsCloudSecurityGroupRulesRepeatCount = 10;

  /**
   * AWS_CLOUD_SECURITY_GROUP_RULES_INBOUND.
   *
   * @var int
   */
  public static $awsCloudSecurityGroupRulesInbound = 0;

  /**
   * AWS_CLOUD_SECURITY_GROUP_RULES_OUTBOUND.
   *
   * @var int
   */
  public static $awsCloudSecurityGroupRulesOutbound = 1;

  /**
   * AWS_CLOUD_SECURITY_GROUP_RULES_MIX.
   *
   * @var int
   */
  public static $awsCloudSecurityGroupRulesMix = 2;

  /**
   * Create test data for cloud service provider (CloudConfig).
   *
   * @return array
   *   Test data.
   */
  protected function createCloudConfigTestFormData(): array {
    $this->random = new Random();
    $cloud_context = $this->random->name(8, TRUE);

    // Input Fields.
    $data = [];
    for ($i = 0, $num = 1; $i < self::$awsCloudConfigRepeatCount; $i++, $num++) {

      $data[] = [
        'name[0][value]'              => sprintf('config-entity-%s - %s', $this->random->name(8, TRUE), date('Y/m/d H:i:s')),
        'field_description[0][value]' => "description #$num: " . date('Y/m/d H:i:s - D M j G:i:s T Y') . $this->random->string(64, TRUE),
        'field_api_version[0][value]' => 'latest',
        'field_region'                => "us-west-$num",
        'field_secret_key[0][value]'  => $this->random->name(20, TRUE),
        'field_access_key[0][value]'  => $this->random->name(40, TRUE),
        'field_account_id[0][value]'  => $this->random->name(16, TRUE),
        'field_get_price_list[value]' => 0,
      ];
    }

    return $data;
  }

  /**
   * Create Elastic IP test data.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return string[][]
   *   Elastic IP array.
   */
  protected function createElasticIpTestFormData($repeat_count): array {
    $data = [];
    // 3 times.
    for ($i = 0, $num = 1; $i < $repeat_count; $i++, $num++) {
      // Input Fields.
      $data[$i] = [
        'name'   => sprintf('eip-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'domain' => 'standard',
      ];
    }
    return $data;
  }

  /**
   * Create network interface test data.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param bool $attachment
   *   Whether attachment is needed.
   *
   * @return array
   *   Array of Network Interface test data.
   *
   * @throws \Exception
   */
  protected function createNetworkInterfaceTestFormData($repeat_count, $attachment = FALSE): array {
    $data = [];

    for ($i = 0, $num = 1; $i < $repeat_count; $i++, $num++) {

      // Input Fields.
      $data[$i] = [
        'name' => $this->random->name(32, TRUE),
        'description' => "Description #$num - {$this->random->name(64, TRUE)}",
        'subnet_id' => "subnet_id-{$this->getRandomId()}",
        'security_groups[]' => "sg-{$this->getRandomId()}",
        'primary_private_ip' => Utils::getRandomPrivateIp(),
      ];

      if ($attachment) {
        $data[$i]['attachment_id'] = "attachment-{$this->getRandomId()}";
      }
    }
    return $data;
  }

  /**
   * Create image test data.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return string[][]
   *   test data array.
   */
  protected function createImageTestFormData($repeat_count): array {
    $data = [];
    for ($i = 0, $num = 1; $i < $repeat_count; $i++, $num++) {

      // Input Fields.
      $data[$i] = [
        'name'        => "Image #$num - " . date('Y/m/d - ') . $this->random->name(8, TRUE),
        'instance_id' => "i-{$this->getRandomId()}",
        'description' => "description-{$this->random->name(64)}",
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
  protected function createInstanceTestFormData($repeat_count = 1): array {
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
        'availability_zone'   => "us-west-${num}",
        'security_groups[]'   => "security_group-$num-{$this->random->name(8, TRUE)}",
        'instance_type'       => "t${num}.micro",
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
   * Create KeyPair test data.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   An array of data.
   */
  protected function createKeyPairTestFormData($repeat_count): array {
    $data = [];

    for ($i = 0; $i < $repeat_count; $i++) {
      // Input Fields.
      $data[$i] = [
        'key_pair_name' => $this->random->name(15, TRUE),
      ];
    }

    return $data;
  }

  /**
   * Create security group test data.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param bool $is_edit
   *   Whether edit mode or not.
   *
   * @return array
   *   Security group test data.
   */
  protected function createSecurityGroupTestFormData($repeat_count = 1, $is_edit = FALSE): array {
    $data = [];

    for ($i = 0, $num = 1; $i < $repeat_count; $i++, $num++) {

      // Input Fields.
      $data[$i] = [
        'description' => "Description #$num - {$this->random->name(64, TRUE)}",
      ];

      if ($is_edit) {
        $data[$i]['name'] = "group-name-#$num - {$this->random->name(15, TRUE)}";
      }
      else {
        $data[$i]['group_name[0][value]'] = "group-name-#$num - {$this->random->name(15, TRUE)}";
      }
    }
    return $data;
  }

  /**
   * Create snapshot test data.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return string[][]
   *   test data array.
   */
  protected function createSnapshotTestFormData($repeat_count): array {
    $data = [];

    for ($i = 0, $num = 1; $i < $repeat_count; $i++, $num++) {

      // Input Fields.
      $data[$i] = [
        'name'        => "Name #$num - {$this->random->name(32, TRUE)}",
        'volume_id'   => "vol-{$this->getRandomId()}",
        'description' => "Description #$num - {$this->random->name(64, TRUE)}",
      ];
    }
    return $data;
  }

  /**
   * Create volume test data.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param bool $is_openstack
   *   Whether openstack or not.
   *
   * @return string[][]|number[][]
   *   test data array.
   */
  protected function createVolumeTestFormData($repeat_count, $is_openstack = FALSE): array {
    $data = [];

    for ($i = 0, $num = 1; $i < $repeat_count; $i++, $num++) {

      // Input Fields.
      $data[$i] = [
        'name'              => "volume-name #$num - {$this->random->name(32, TRUE)}",
        'snapshot_id'       => "snap-{$this->getRandomId()}",
        'size'              => $num * 10,
      ];

      if ($is_openstack) {
        $data[$i]['availability_zone'] = 'RegionOne';
        $data[$i]['volume_type'] = 'lvmdriver-1';
      }
      else {
        $data[$i]['availability_zone'] = "us-west-${num}";
        $data[$i]['iops'] = $num * 1000;
        $data[$i]['encrypted'] = $num % 2;
        $data[$i]['volume_type'] = 'io1';
      }
    }
    return $data;
  }

  /**
   * Create VPC test data.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   *
   * @throws \Exception
   */
  protected function createVpcTestFormData($repeat_count = 1): array {
    $data = [];

    for ($i = 0, $num = 1; $i < $repeat_count; $i++, $num++) {

      // Input Fields.
      $data[] = [
        'name'        => "VPC #$num - {$this->random->name(32, TRUE)}",
        'cidr_block'   => Utils::getRandomCidr(),
      ];
    }
    return $data;
  }

  /**
   * Create VPC Peering Connection test data.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param array $vpc_ids
   *   VPC IDs.
   *
   * @return array
   *   Test data.
   */
  protected function createVpcPeeringConnectionTestFormData($repeat_count, array $vpc_ids): array {
    $data = [];

    for ($i = 0, $num = 1; $i < $repeat_count; $i++, $num++) {

      // Input Fields.
      $data[] = [
        'name'              => "VPC Peering Connection #$num - {$this->random->name(32, TRUE)}",
        'requester_vpc_id'  => $vpc_ids[$i],
        'accepter_vpc_id'   => $vpc_ids[$i],
      ];
    }
    return $data;
  }

  /**
   * Create subnet test data.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param array $vpc_ids
   *   VPC IDs.
   *
   * @return array
   *   Test data.
   *
   * @throws \Exception
   */
  protected function createSubnetTestFormData($repeat_count, array $vpc_ids): array {
    $data = [];

    for ($i = 0, $num = 1; $i < $repeat_count; $i++, $num++) {

      // Input Fields.
      $data[] = [
        'vpc_id'      => $vpc_ids[$i],
        'name'        => "Name #$num - {$this->random->name(32, TRUE)}",
        'cidr_block'  => Utils::getRandomCidr(),
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
  protected function createServerTemplateTestFormData($repeat_count = 1): array {
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
        'field_instance_type' => "c{$instance_family}.xlarge",
        'field_availability_zone' => 'us-west-1',
        'field_monitoring[value]' => '1',
        'field_image_id' => "ami-{$this->getRandomId()}",
        'field_min_count[0][value]' => 1,
        'field_max_count[0][value]' => 1,
        'field_kernel_id[0][value]' => "aki-{$this->getRandomId()}",
        'field_ram[0][value]' => "ari-{$this->getRandomId()}",
        'field_security_group' => 1,
        'field_ssh_key' => 1,
        'field_tags[0][item_key]' => "key_{$random->name(8, TRUE)}",
        'field_tags[0][item_value]' => "value_{$random->name(8, TRUE)}",
      ];
    }
    return $data;
  }

  /**
   * Create random snapshots data.
   *
   * @return array
   *   Random snapshots.
   *
   * @throws \Exception
   */
  protected function createSnapshotsRandomTestFormData(): array {
    $snapshots = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $snapshots[] = [
        'SnapshotId' => "snap-{$this->getRandomId()}",
        'Name' => sprintf('snapshot-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $snapshots;
  }

  /**
   * Create random volumes data.
   *
   * @return array
   *   Random volumes data.
   *
   * @throws \Exception
   */
  protected function createVolumesRandomTestFormData(): array {
    $volumes = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $volumes[] = [
        'VolumeId' => "vol-{$this->getRandomId()}",
        'Name' => sprintf('volume-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $volumes;
  }

  /**
   * Create random images data.
   *
   * @return array
   *   Random images data.
   *
   * @throws \Exception
   */
  protected function createImagesRandomTestFormData(): array {
    $images = [];
    $architecture = ['x86_64', 'arm64'];
    $image_type = ['machine', 'kernel', 'ramdisk'];
    $state = ['available', 'pending', 'failed'];
    $hypervisor = ['ovm', 'xen'];
    $public = [0, 1];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $images[] = [
        'ImageId' => "ami-{$this->getRandomId()}",
        'Name' => sprintf('image-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'BlockDeviceMappings' => [],
        'OwnerId' => $this->random->name(8, TRUE),
        'Architecture' => $architecture[array_rand($architecture)],
        'ImageType' => $image_type[array_rand($image_type)],
        'State' => $state[array_rand($state)],
        'Hypervisor' => $hypervisor[array_rand($hypervisor)],
        'Public' => $public[array_rand($public)],
        'ProductCodes' => [
          ['ProductCode' => $this->random->name(8, TRUE)],
          ['ProductCode' => $this->random->name(8, TRUE)],
        ],
      ];
    }

    return $images;
  }

  /**
   * Create random instances data.
   *
   * @return array
   *   Random instances data.
   *
   * @throws \Exception
   */
  protected function createInstancesRandomTestFormData(): array {
    $instances = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $instances[] = [
        'InstanceId' => "i-{$this->getRandomId()}",
        'Name' => sprintf('instance-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $instances;
  }

  /**
   * Create random VPCs data.
   *
   * @return array
   *   Random VPCs data.
   *
   * @throws \Exception
   */
  protected function createVpcsRandomTestFormData(): array {
    $vpcs = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $vpcs[] = [
        'VpcId' => "vpc-{$this->getRandomId()}",
        'Name' => sprintf('vpc-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'CidrBlock' => Utils::getRandomCidr(),
        'DhcpOptionsId' => 'dopt-' . $this->getRandomId(),
        'InstanceTenancy' => 'default',
        'IsDefault' => FALSE,
        'State' => 'available',
        'Tags' => [
          [
            'Key' => 'Name',
            'Value' => sprintf('vpc-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
          ],
        ],
      ];
    }

    return $vpcs;
  }

  /**
   * Create random VPC Peering Connections data.
   *
   * @return array
   *   Random VPCs data.
   *
   * @throws \Exception
   */
  protected function createVpcPeeringConnectionsRandomTestFormData(): array {
    $vpc_peering_connections = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $vpc_peering_connections[] = [
        'VpcPeeringConnectionId' => "pcx-{$this->getRandomId()}",
        'Name' => sprintf('pcx-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'Tags' => [
          [
            'Key' => 'Name',
            'Value' => sprintf('pcx-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
          ],
        ],
      ];
    }

    return $vpc_peering_connections;
  }

  /**
   * Create random Network Interfaces data.
   *
   * @return array
   *   Random network interfaces data.
   *
   * @throws \Exception
   */
  protected function createNetworkInterfacesRandomTestFormData(): array {
    $network_interfaces = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $network_interfaces[] = [
        'NetworkInterfaceId' => "eni-{$this->getRandomId()}",
        'Name' => sprintf('eni-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $network_interfaces;
  }

  /**
   * Create random security groups data.
   *
   * @return array
   *   Random security groups data.
   *
   * @throws \Exception
   */
  protected function createSecurityGroupRandomTestFormData(): array {
    $security_groups = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $security_groups[] = [
        'GroupId' => "sg-{$this->getRandomId()}",
        'GroupName' => sprintf('sg-random-data #%d-%s-%s', $i, date('Y/m/d-H:i:s'), $this->random->name(8, TRUE)),
        'Name' => sprintf('sg-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $security_groups;
  }

  /**
   * Create random Elastic IPs data.
   *
   * @return array
   *   Random Elastic IPs.
   *
   * @throws \Exception
   */
  protected function createElasticIpRandomTestFormData(): array {
    $elastic_ips = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $elastic_ips[] = [
        'PublicIp' => Utils::getRandomPublicIp(),
        'Name' => sprintf('eip-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $elastic_ips;
  }

  /**
   * Create random key pairs data.
   *
   * @return array
   *   Random key pairs data.
   *
   * @throws \Exception
   */
  protected function createKeyPairsRandomTestFormData(): array {

    $key_pairs = [];
    $count = random_int(1, 10);

    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {

      $key_fingerprint_parts = [];
      for ($part = 0; $part < 20; $part++) {
        $key_fingerprint_parts[] = sprintf('%02x', random_int(0, 255));
      }

      $key_pairs[] = [
        'KeyFingerprint' => implode(':', $key_fingerprint_parts),
        'Name' => sprintf('key_pair-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $key_pairs;
  }

  /**
   * Create random IAM roles.
   *
   * @return array
   *   Random IAM roles.
   *
   * @throws \Exception
   */
  protected function createIamRolesRandomTestFormData(): array {
    $random = $this->random;
    $iam_roles = [];
    $count = random_int(1, 10);
    for ($i = 0; $i < $count; $i++) {
      $arn_num = sprintf('%012s', random_int(1, 999999999999));
      $arn_name = $random->name(16, TRUE);
      $name = $random->name(10, TRUE);
      $iam_roles[] = [
        'InstanceProfileName' => $name,
        'Arn' => "arn:aws:iam::$arn_num:instance-profile/$arn_name",
        'Roles' => [
          ['RoleName' => $name],
        ],
      ];

    }

    return $iam_roles;
  }

  /**
   * Create random subnets.
   *
   * @return array
   *   Random subnets array.
   *
   * @throws \Exception
   */
  protected function createSubnetsRandomTestFormData(): array {
    $subnets = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $subnets[] = [
        'SubnetId' => "subnet-{$this->getRandomId()}",
        'Name' => sprintf('subnet-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'CidrBlock' => Utils::getRandomCidr(),
        'State' => 'available',
        'Tags' => [
          [
            'Key' => 'Name',
            'Value' => sprintf('subnet-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
          ],
        ],
      ];
    }

    return $subnets;
  }

  /**
   * Create rules.
   *
   * @param int $rules_type
   *   The type of rules. Inbound | Outbound | Mixed.
   * @param string $edit_url
   *   The URL of security group edit form.
   * @param string $self_group_id
   *   The security group ID for rules.
   * @param int $security_group_rules_repeat_count
   *   The security group rules repeat count.
   *
   * @return array
   *   The rules created.
   *
   * @throws \Exception
   */
  protected function createRulesTestFormData($rules_type, $edit_url, $self_group_id = NULL, $security_group_rules_repeat_count = 1): array {
    $rules = [];
    $count = random_int(1, $security_group_rules_repeat_count);
    for ($i = 0; $i < $count; $i++) {
      if (isset($self_group_id)) {
        $group_arr = [$self_group_id, "sg-{$this->getRandomId()}"];
        $group_id = $group_arr[array_rand($group_arr)];
      }
      else {
        $group_id = "sg-{$this->getRandomId()}";
      }
      $permissions = [
        [
          'source' => 'ip4',
          'cidr_ip' => Utils::getRandomCidr(),
          'from_port' => Utils::getRandomFromPort(),
          'to_port' => Utils::getRandomToPort(),
        ],
        [
          'source' => 'ip6',
          'cidr_ip_v6' => Utils::getRandomCidrV6(),
          'from_port' => Utils::getRandomFromPort(),
          'to_port' => Utils::getRandomToPort(),
        ],
      ];

      if ($rules_type === self::$awsCloudSecurityGroupRulesInbound
        || $rules_type === self::$awsCloudSecurityGroupRulesOutbound) {
        $type = $rules_type;
      }
      else {
        $types = [self::$awsCloudSecurityGroupRulesInbound, self::$awsCloudSecurityGroupRulesOutbound];
        $type = $types[array_rand($types)];
      }
      $rules[] = $permissions[array_rand($permissions)] + ['type' => $type];
    }

    // Post to form.
    $params = [];
    $inbound_index = 0;
    $outbound_index = 0;
    $rules_added = [];
    foreach ($rules ?: [] as $rule) {
      if ($rule['type'] === self::$awsCloudSecurityGroupRulesInbound) {
        $index = $inbound_index++;
        $prefix = 'ip_permission';
      }
      else {
        $index = $outbound_index++;
        $prefix = 'outbound_permission';
      }

      foreach ($rule ?: [] as $key => $value) {
        if ($key === 'type') {
          continue;
        }
        $params["${prefix}[${index}][${key}]"] = $value;
      }

      $rules_added[] = $rule;
      $this->updateRulesMockData($rules_added, self::$awsCloudSecurityGroupRulesOutbound);

      $this->drupalPostForm($edit_url, $params, $this->t('Save'));
      $this->assertSession()->pageTextContains($rule['from_port']);
    }

    // Confirm the values of edit form.
    $this->confirmRulesFormData($rules, $edit_url);

    return $rules;
  }

  /**
   * Revoke rules.
   *
   * @param array $rules
   *   The rules to be revoked.
   * @param string $view_url
   *   The URL of security group detailed view.
   */
  protected function revokeRulesTestFormData(array $rules, $view_url): void {
    $this->drupalGet($view_url);
    $count = count($rules);
    $inbound_rules = array_filter($rules, static function ($a) {
      return $a['type'] === self::$awsCloudSecurityGroupRulesInbound;
    });
    $inbound_rules_count = count($inbound_rules);
    for ($i = 0; $i < $count; $i++) {
      $rule = array_shift($rules);

      $index = 0;
      if ($rule['type'] === self::$awsCloudSecurityGroupRulesOutbound) {
        $index += $inbound_rules_count;
      }
      else {
        $inbound_rules_count--;
      }

      $this->clickLink(t('Revoke'), $index);
      $this->assertSession()->pageTextContains($rule['from_port']);

      $this->updateRulesMockData($rules, self::$awsCloudSecurityGroupRulesOutbound);
      $this->submitForm([], $this->t('Revoke'));
      $this->assertSession()->pageTextContains('Permission revoked');
    }
  }

  /**
   * Confirm rule values of security group edit form.
   *
   * @param array $rules
   *   The array of rules.
   * @param string $edit_url
   *   The url of edit form.
   */
  protected function confirmRulesFormData(array $rules, $edit_url): void {
    $this->drupalGet($edit_url);

    $inbound_index = 0;
    $outbound_index = 0;
    foreach ($rules ?: [] as $rule) {
      if ($rule['type'] === self::$awsCloudSecurityGroupRulesInbound) {
        $index = $inbound_index++;
        $prefix = 'ip_permission';
      }
      else {
        $index = $outbound_index++;
        $prefix = 'outbound_permission';
      }
      foreach ($rule ?: [] as $key => $value) {
        if ($key === 'type') {
          continue;
        }
        $name = "${prefix}[${index}][${key}]";
        $this->assertSession()->fieldValueEquals($name, $value);
      }
    }

    // Confirm the last raw is empty.
    $name = "ip_permission[${inbound_index}][from_port]";
    $this->assertSession()->fieldValueEquals($name, '');
    $name = "outbound_permission[${outbound_index}][from_port]";
    $this->assertSession()->fieldValueEquals($name, '');
  }

  /**
   * Create random subnets.
   *
   * @return array
   *   Random subnets array.
   *
   * @throws \Exception
   */
  protected function createRandomSubnets(): array {

    $subnets = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $subnets[] = [
        'SubnetId' => 'subnet-' . $this->getRandomId(),
        'Tags' => [
          [
            'Key' => 'Name',
            'Value' => sprintf('subnet-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
          ],
        ],
      ];
    }

    return $subnets;
  }

  /**
   * Create update snapshot test cases.
   *
   * @return string[][]
   *   test cases array.
   */
  protected function createUpdateSnapshotTestCases(): array {
    $test_cases = [];
    $random = $this->random;

    // Only ID.
    $test_cases[] = ['id' => 'snap-' . $this->getRandomId()];

    // Name and ID.
    $test_cases[] = [
      'name' => 'Snapshot Name ' . $random->name(32, TRUE),
      'id' => 'snap-' . $this->getRandomId(),
    ];

    return $test_cases;
  }

  /**
   * Get random rule.
   *
   * @param array $rule
   *   The array of rule.
   * @param bool $include_group
   *   Boolean to include group rule.
   *
   * @throws \Exception
   */
  protected function getRandomRule(array &$rule, $include_group = TRUE): void {
    $sources = ['ip4', 'ip6'];
    // Group can cause testing issues because a valid group_id is required
    // for AWS tests.  Added a boolean flag to disable group permissions from
    // being included in the rules.
    if ($include_group === TRUE) {
      $sources[] = 'group';
    }
    $rule = ['type' => $rule['type']];
    $source = $sources[array_rand($sources)];
    $rule['source'] = $source;
    $rule['from_port'] = Utils::getRandomFromPort();
    $rule['to_port'] = Utils::getRandomToPort();
    if ($source === 'ip4') {
      $rule['cidr_ip'] = Utils::getRandomCidr();
    }
    elseif ($source === 'ip6') {
      $rule['cidr_ip_v6'] = Utils::getRandomCidrV6();
    }
    else {
      $rule['user_id'] = $this->random->name(8, TRUE);
      $rule['group_id'] = "sg-{$this->getRandomId()}";
      $rule['vpc_id'] = "vpc-{$this->getRandomId()}";
      $rule['peering_connection_id'] = "pcx-{$this->getRandomId()}";
      $rule['peering_status'] = 'active';
    }

  }

  /**
   * Create random state.
   *
   * @return string
   *   random state.
   */
  protected function createRandomState(): string {
    $states = ['creating', 'in-use'];
    return $states[array_rand($states)];
  }

  /**
   * Get name from array.
   *
   * @param array $array
   *   The VPCs or subnets array.
   * @param int $index
   *   The index of array.
   * @param string $id_key_name
   *   The key name of index.
   *
   * @return string
   *   value of array.
   */
  protected function getNameFromArray(array $array, $index, $id_key_name): string {
    // Get ID.
    return $array[$index][$id_key_name];
  }

  /**
   * Get Random domain.
   *
   * @return string
   *   a random domain.
   */
  protected function getRandomDomain(): string {
    $domains = ['standard', 'vpc'];
    return $domains[array_rand($domains)];
  }

}
