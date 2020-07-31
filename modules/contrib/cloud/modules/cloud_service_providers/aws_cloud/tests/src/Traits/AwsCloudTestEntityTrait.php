<?php

namespace Drupal\Tests\aws_cloud\Traits;

use Drupal\aws_cloud\Entity\Ec2\ElasticIp;
use Drupal\aws_cloud\Entity\Ec2\Image;
use Drupal\aws_cloud\Entity\Vpc\Subnet;
use Drupal\aws_cloud\Entity\Vpc\Vpc;
use Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnection;
use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\cloud\Entity\CloudServerTemplate;
use Drupal\Tests\cloud\Functional\Utils;

/**
 * The trait creating test entity for aws cloud testing.
 */
trait AwsCloudTestEntityTrait {

  /**
   * Create an AWS Cloud Instance test entity.
   *
   * @param string $class
   *   The Instance class.
   * @param int $num
   *   The index.
   * @param array $regions
   *   The Regions.
   * @param string $public_ip
   *   The public IP.
   * @param string $instance_name
   *   The Instance name.
   * @param string $instance_id
   *   The Instance ID.
   * @param string $instance_state
   *   The Instance state.
   *
   * @return object
   *   The Instance entity.
   *
   * @throws \Exception
   */
  protected function createInstanceTestEntity($class, $num = 0, array $regions = [], $public_ip = NULL, $instance_name = '', $instance_id = '', $instance_state = 'running') {
    if (!isset($public_ip)) {
      $public_ip = Utils::getRandomPublicIp();
    }
    $private_ip = Utils::getRandomPrivateIp();
    $region = $regions[array_rand($regions)];

    return $this->createTestEntity($class, [
      'cloud_context' => $this->cloudContext,
      'name' => $instance_name ?: sprintf('instance-entity #%d - %s - %s', $num + 1, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      'image_id' => 'ami-' . $this->getRandomId(),
      'key_pair_name' => "key_pair-{$this->random->name(8, TRUE)}",
      'is_monitoring' => 0,
      'availability_zone' => "us-west-$num",
      'security_groups' => "security_group-{$this->random->name(8, TRUE)}",
      'instance_type' => "t$num.small",
      'kernel_id' => 'aki-' . $this->getRandomId(),
      'ramdisk_id' => 'ari-' . $this->getRandomId(),
      'user_data' => "User Data #$num: {$this->random->string(64, TRUE)}",
      'account_id' => random_int(100000000000, 999999999999),
      'reservation_id' => 'r-' . $this->getRandomId(),
      'group_name' => $this->random->name(8, TRUE),
      'host_id' => $this->random->name(8, TRUE),
      'affinity' => $this->random->name(8, TRUE),
      'launch_time' => date('c'),
      'security_group_id' => "sg-{$this->getRandomId()}",
      'security_group_name' => $this->random->name(10, TRUE),
      'public_dns_name' => Utils::getPublicDns($region, $public_ip),
      'public_ip_address' => $public_ip,
      'private_dns_name' => Utils::getPrivateDns($region, $private_ip),
      'private_ip_address' => $private_ip,
      'vpc_id' => 'vpc-' . $this->getRandomId(),
      'subnet_id' => 'subnet-' . $this->getRandomId(),
      'reason' => $this->random->string(16, TRUE),
      'instance_id' => $instance_id ?: "i-{$this->getRandomId()}",
      'instance_state' => $instance_state,
      'uid' => $this->loggedInUser->id(),
    ]);
  }

  /**
   * Create an AWS Cloud VPC test entity.
   *
   * @param int $index
   *   The VPC index.
   * @param string $vpc_id
   *   The VPC ID.
   * @param string $vpc_name
   *   The VPC name.
   * @param string $cloud_context
   *   The Cloud context.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The VPC entity.
   */
  protected function createVpcTestEntity($index = 0, $vpc_id = '', $vpc_name = '', $cloud_context = ''): CloudContentEntityBase {

    return $this->createTestEntity(Vpc::class, [
      'cloud_context' => $cloud_context ?: $this->cloudContext,
      'vpc_id' => $vpc_id ?: 'vpc-' . $this->getRandomId(),
      'name' => $vpc_name ?: sprintf('vpc-entity #%d - %s - %s', $index + 1, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
    ]);
  }

  /**
   * Create an AWS Cloud VPC Peering Connection test entity.
   *
   * @param int $index
   *   The VPC Peering Connection index.
   * @param string $vpc_peering_connection_id
   *   The VPC Peering Connection ID.
   * @param string $vpc_peering_connection_ame
   *   The VPC Peering Connection name.
   * @param string $cloud_context
   *   The Cloud context.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The VPC entity.
   */
  protected function createVpcPeeringConnectionTestEntity($index = 0, $vpc_peering_connection_id = '', $vpc_peering_connection_ame = '', $cloud_context = ''): CloudContentEntityBase {
    return $this->createTestEntity(VpcPeeringConnection::class, [
      'cloud_context' => $cloud_context ?: $this->cloudContext,
      'vpc_peering_connection_id' => $vpc_peering_connection_id ?: 'pcx-' . $this->getRandomId(),
      'name' => $vpc_peering_connection_ame ?: sprintf('pcx-entity #%d - %s - %s', $index + 1, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
    ]);
  }

  /**
   * Create an AWS Cloud Subnet test entity.
   *
   * @param int $index
   *   The Subnet index.
   * @param string $subnet_id
   *   The Subnet ID.
   * @param string $subnet_name
   *   The Subnet name.
   * @param string $cloud_context
   *   The Cloud context.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The Subnet entity.
   */
  protected function createSubnetTestEntity($index = 0, $subnet_id = '', $subnet_name = '', $cloud_context = ''): CloudContentEntityBase {

    return $this->createTestEntity(Subnet::class, [
      'cloud_context' => $cloud_context ?: $this->cloudContext,
      'subnet_id' => $subnet_id ?: 'subnet-' . $this->getRandomId(),
      'name' => $subnet_name ?: sprintf('subnet-entity #%d - %s - %s', $index + 1, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
    ]);
  }

  /**
   * Create an AWS Cloud Network Interface test entity.
   *
   * @param string $class
   *   The Network Interface class.
   * @param int $index
   *   The Index.
   * @param string $network_interface_id
   *   The Network Interface ID.
   * @param string $network_interface_name
   *   The Network Interface name.
   * @param string $instance_id
   *   The Instance ID.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The Network Interface entity.
   *
   * @throws \Exception
   */
  protected function createNetworkInterfaceTestEntity($class, $index = 0, $network_interface_id = '', $network_interface_name = '', $instance_id = ''): CloudContentEntityBase {
    $timestamp = time();
    $private_ip = Utils::getRandomPrivateIp();
    $secondary_private_ip = Utils::getRandomPrivateIp();

    return $this->createTestEntity($class, [
      'cloud_context' => $this->cloudContext,
      'name' => $network_interface_name ?: sprintf('eni-entity #%d - %s - %s', $index + 1, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      'network_interface_id' => $network_interface_id ?: 'eni-' . $this->getRandomId(),
      'vpc_id' => "vpc-{$this->random->name(8, TRUE)}",
      'mac_address' => NULL,
      'security_groups' => "security_group-{$this->random->name(8, TRUE)}",
      'status' => 'in-use',
      'private_dns' => NULL,
      'primary_private_ip' => $private_ip,
      'secondary_private_ips' => [$secondary_private_ip],
      'attachment_id' => 'attachment-' . $this->getRandomId(),
      'attachment_owner' => NULL,
      'attachment_status' => NULL,
      'owner_id' => random_int(100000000000, 999999999999),
      'association_id' => NULL,
      'secondary_association_id' => NULL,
      'subnet_id' => NULL,
      'description' => NULL,
      'public_ips' => NULL,
      'source_dest_check' => NULL,
      'instance_id' => $instance_id ?: 'i-' . $this->getRandomId(),
      'device_index' => NULL,
      'delete_on_termination' => NULL,
      'allocation_id' => NULL,
      'created' => $timestamp,
      'changed' => $timestamp,
      'refreshed' => $timestamp,
    ]);
  }

  /**
   * Create an AWS Cloud Elastic IP test entity.
   *
   * @param int $index
   *   The Elastic IP index.
   * @param string $elastic_ip_name
   *   The Elastic IP name.
   * @param string $public_ip
   *   The public IP address.
   * @param string $cloud_context
   *   The cloud context.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The Elastic IP entity.
   *
   * @throws \Exception
   */
  protected function createElasticIpTestEntity($index = 0, $elastic_ip_name = '', $public_ip = '', $cloud_context = ''): CloudContentEntityBase {
    $timestamp = time();

    return $this->createTestEntity(ElasticIp::class, [
      'cloud_context' => $cloud_context ?: $this->cloudContext,
      'name' => $elastic_ip_name ?: sprintf('eip-entity #%d - %s - %s', $index + 1, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      'public_ip' => $public_ip ?: Utils::getRandomPublicIp(),
      'instance_id' => NULL,
      'network_interface_id' => NULL,
      'private_ip_address' => NULL,
      'network_interface_owner' => NULL,
      'allocation_id' => NULL,
      'association_id' => NULL,
      'domain' => 'standard',
      'created' => $timestamp,
      'changed' => $timestamp,
      'refreshed' => $timestamp,
    ]);
  }

  /**
   * Create an AWS Cloud Snapshot test entity.
   *
   * @param string $class
   *   The Snapshot class.
   * @param int $index
   *   The index.
   * @param string $snapshot_id
   *   The Snapshot ID.
   * @param string $snapshot_name
   *   The Snapshot name.
   * @param string $cloud_context
   *   The Cloud context.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The snapshot entity.
   */
  protected function createSnapshotTestEntity($class, $index = 0, $snapshot_id = '', $snapshot_name = '', $cloud_context = ''): CloudContentEntityBase {
    return $this->createTestEntity($class, [
      'cloud_context' => $cloud_context,
      'snapshot_id' => $snapshot_id,
      'name' => $snapshot_name ?: sprintf('snapshot-entity #%d - %s - %s', $index + 1, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      'created' => time(),
    ]);
  }

  /**
   * Create an AWS Cloud Volume test entity.
   *
   * @param string $class
   *   The Volume class.
   * @param int $num
   *   The Volume number.
   * @param string $volume_id
   *   The Volume ID.
   * @param string $volume_name
   *   The Volume name.
   * @param string $cloud_context
   *   The Cloud context.
   * @param int $uid
   *   The User ID.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The Volume entity.
   */
  protected function createVolumeTestEntity($class, $num, $volume_id, $volume_name, $cloud_context, $uid): CloudContentEntityBase {
    return $this->createTestEntity($class, [
      'cloud_context'     => $cloud_context,
      'volume_id'         => $volume_id,
      'name'              => $volume_name ?: sprintf('volume-entity #%d - %s - %s', $num + 1, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      'uid' => $uid,
      'size'              => $num * 10,
      'availability_zone' => "us-west-$num",
      'iops'              => $num * 1000,
      'encrypted'         => $num % 2,
      'volume_type'       => 'io1',
      'volume_status'     => 'Available',
      'state' => 'available',
    ]);
  }

  /**
   * Create an AWS Cloud Security Group test entity.
   *
   * @param string $class
   *   The Security Group class.
   * @param int $index
   *   The Security Group index.
   * @param string $group_id
   *   The Security Group.
   * @param string $group_name
   *   The Security Group name.
   * @param string $vpc_id
   *   The VPC ID.
   * @param string $cloud_context
   *   The Cloud context.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The Security Group entity.
   */
  protected function createSecurityGroupTestEntity($class, $index = 0, $group_id = '', $group_name = '', $vpc_id = '', $cloud_context = ''): CloudContentEntityBase {

    $group_name = $group_name ?: sprintf('sg-entity #%d - %s - %s', $index + 1, date('Y/m/d H:i:s'), $this->random->name(8, TRUE));

    return $this->createTestEntity($class, [
      'name'          => $group_name,
      'group_name'    => $group_name,
      'group_id'      => $group_id ?: 'sg-' . $this->getRandomId(),
      'vpc_id'        => $vpc_id,
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * Create an AWS Cloud Image test entity.
   *
   * @param string $class
   *   The Image class.
   * @param int $index
   *   The Image index.
   * @param string $image_id
   *   The Image ID.
   * @param string $cloud_context
   *   Tne Cloud context.
   *
   * @return \Drupal\aws_cloud\Entity\Ec2\Image
   *   The Image entity.
   */
  protected function createImageTestEntity($class, $index = 0, $image_id = '', $cloud_context = ''): CloudContentEntityBase {
    $name = sprintf('image-entity #%d - %s - %s', $index + 1, date('Y/m/d H:i:s'), $this->random->name(32, TRUE));
    return $this->createTestEntity($class, [
      'cloud_context' => $cloud_context,
      'image_id' => $image_id ?: "ami-{$this->getRandomId()}",
      'name' => $name,
      'ami_name' => $name,
      'root_device_type' => 'ebs',
      'created' => time(),
    ]);
  }

  /**
   * Create an AWS Cloud Key Pair test entity.
   *
   * @param string $class
   *   The Key Pair class.
   * @param int $index
   *   The Key Pair index.
   * @param string $key_pair_name
   *   The Key Pair Name.
   * @param string $key_fingerprint
   *   The Fingerprint.
   * @param string $cloud_context
   *   The Cloud context.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The Key Pair entity.
   */
  protected function createKeyPairTestEntity($class, $index = 0, $key_pair_name = '', $key_fingerprint = '', $cloud_context = ''): CloudContentEntityBase {
    return $this->createTestEntity($class, [
      'cloud_context' => $cloud_context,
      'key_pair_name' => $key_pair_name ?: sprintf('key_pair-entity #%d - %s - %s', $index + 1, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      'key_fingerprint' => $key_fingerprint,
      'created' => time(),
    ]);
  }

  /**
   * Create Cloud Server Template.
   *
   * @param array $iam_roles
   *   The IAM Roles.
   * @param \Drupal\aws_cloud\Entity\Ec2\Image $image
   *   The Image Entity.
   * @param string $cloud_context
   *   The cloud context.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The CloudServerTemplate entity.
   */
  protected function createServerTemplateTestEntity(array $iam_roles,
                                                    Image $image,
                                                    $cloud_context): CloudContentEntityBase {
    // Create template.
    $template = $this->createTestEntity(CloudServerTemplate::class, [
      'cloud_context' => $cloud_context,
      'type' => 'aws_cloud',
      'name' => 'test_template1',
    ]);

    $template->field_test_only->value = '1';
    $template->field_max_count->value = 1;
    $template->field_min_count->value = 1;
    $template->field_monitoring->value = '0';
    $template->field_instance_type->value = 't3.micro';
    $template->field_image_id->value = $image->getImageId()
      ?: "ami-{$this->getRandomId()}";

    // Set the IAM role which can be NULL or something.
    $iam_role_index = array_rand($iam_roles);
    if ($iam_role_index === 0) {
      $template->field_iam_role = NULL;
    }
    else {

      // @TODO: Is this correct? Make sure if field_iam_role->value is correct or not
      $template->field_iam_role = str_replace(
        'role/',
        'instance-profile/',
        $iam_roles[$iam_role_index]['Arn']
      );
    }

    $template->save();
    return $template;
  }

}
