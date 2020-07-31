<?php

namespace Drupal\aws_cloud\Service\Ec2;

use Drupal\aws_cloud\Entity\Ec2\ElasticIp;
use Drupal\aws_cloud\Entity\Ec2\Image;
use Drupal\aws_cloud\Entity\Ec2\Instance;
use Drupal\aws_cloud\Entity\Ec2\KeyPair;
use Drupal\aws_cloud\Entity\Ec2\NetworkInterface;
use Drupal\aws_cloud\Entity\Ec2\SecurityGroup;
use Drupal\aws_cloud\Entity\Ec2\Snapshot;
use Drupal\aws_cloud\Entity\Ec2\Volume;
use Drupal\aws_cloud\Entity\Vpc\Subnet;
use Drupal\aws_cloud\Entity\Vpc\Vpc;
use Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnection;
use Drupal\cloud\Entity\CloudServerTemplate;

/**
 * Entity update methods for Batch API processing.
 */
class Ec2BatchOperations {

  /**
   * The finish callback function.
   *
   * Deletes stale entities from the database.
   *
   * @param string $entity_type
   *   The entity type.
   * @param array $stale
   *   The stale entities to delete.
   * @param bool $clear
   *   TRUE to clear entities, FALSE keep them.
   */
  public static function finished($entity_type, array $stale, $clear = TRUE) {
    $entity_type_manager = \Drupal::entityTypeManager();
    if (count($stale) && $clear === TRUE) {
      $entity_type_manager->getStorage($entity_type)->delete($stale);
    }
  }

  /**
   * Update or create an instance entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $instance
   *   The instance array.
   */
  public static function updateInstance($cloud_context, array $instance) {
    /* @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface */
    $ec2Service = \Drupal::service('aws_cloud.ec2');
    $ec2Service->setCloudContext($cloud_context);
    $timestamp = time();

    $config_factory = \Drupal::configFactory();

    // Get instance IAM roles associated to instances.
    $instance_iam_roles = [];
    $associations_result = $ec2Service->describeIamInstanceProfileAssociations();
    foreach ($associations_result['IamInstanceProfileAssociations'] ?: [] as $association) {
      $instance_iam_roles[$association['InstanceId']]
        = $association['IamInstanceProfile']['Arn'];
    }

    // Get volumeIDs associated to instances.
    $block_devices = [];
    if (isset($instance['BlockDeviceMappings'])) {
      foreach ($instance['BlockDeviceMappings'] ?: [] as $block_device) {
        $block_devices[] = $block_device['Ebs']['VolumeId'];
      }
    }

    $instanceName = '';
    $uid = 0;
    $termination_timestamp = NULL;
    $schedule = '';
    $tags = [];
    if (!isset($instance['Tags'])) {
      $instance['Tags'] = [];
    }
    foreach ($instance['Tags'] ?: [] as $tag) {
      if ($tag['Key'] === 'Name') {
        $instanceName = $tag['Value'];
      }
      if ($tag['Key'] === 'aws_cloud_' . Instance::TAG_LAUNCHED_BY_UID) {
        $uid = $tag['Value'];
      }
      if ($tag['Key'] === 'aws_cloud_' . Instance::TAG_TERMINATION_TIMESTAMP) {
        if ($tag['Value'] !== '') {
          $termination_timestamp = (int) $tag['Value'];
        }
      }
      if ($tag['Key'] === $config_factory->get('aws_cloud.settings')->get('aws_cloud_scheduler_tag')) {
        $schedule = $tag['Value'];
      }

      $tags[] = ['item_key' => $tag['Key'], 'item_value' => $tag['Value']];
    }

    usort($tags, static function ($a, $b) {
      if ($a['item_key'] === 'Name') {
        return -1;
      }

      if ($b['item_key'] === 'Name') {
        return 1;
      }

      return strcmp($a['item_key'], $b['item_key']);
    });

    // Default to instance_id.
    if (empty($instanceName)) {
      $instanceName = $instance['InstanceId'];
    }

    $security_groups = [];
    foreach ($instance['SecurityGroups'] ?: [] as $security_group) {
      $security_groups[] = $security_group['GroupName'];
    }

    // Termination protection.
    $attribute_result = $ec2Service->describeInstanceAttribute([
      'InstanceId' => $instance['InstanceId'],
      'Attribute' => 'disableApiTermination',
    ]);
    $termination_protection = $attribute_result['DisableApiTermination']['Value'];

    // Get user data.
    $attribute_result = $ec2Service->describeInstanceAttribute([
      'InstanceId' => $instance['InstanceId'],
      'Attribute' => 'userData',
    ]);
    $user_data = '';
    if (!empty($attribute_result['UserData']['Value'])) {
      $user_data = base64_decode($attribute_result['UserData']['Value']);
    }

    // Instance IAM roles.
    $iam_role = $instance_iam_roles[$instance['InstanceId']] ?? NULL;

    // Use NetworkInterface to look up private IPs.  In EC2-VPC,
    // an instance can have more than one private IP.
    $network_interfaces = [];
    $private_ips = FALSE;

    if (isset($instance['NetworkInterfaces'])) {
      $private_ips = $ec2Service->getPrivateIps($instance['NetworkInterfaces']);
      foreach ($instance['NetworkInterfaces'] ?: [] as $interface) {
        $network_interfaces[] = $interface['NetworkInterfaceId'];
      }
    }

    // Get instance types.
    $instance_types = aws_cloud_get_instance_types($cloud_context);
    $entity_id = $ec2Service->getEntityId('aws_cloud_instance', 'instance_id', $instance['InstanceId']);
    $cost = $ec2Service->calculateInstanceCost($instance, $instance_types);
    // Skip if $entity already exists, by updating 'refreshed' time.
    if (!empty($entity_id)) {
      /* @var \Drupal\aws_cloud\Entity\Ec2\Instance $entity */
      $entity = Instance::load($entity_id);
      $entity->setName($instanceName);
      $entity->setInstanceState($instance['State']['Name']);

      // Set attributes that are available when system starts up.
      $public_ip = NULL;

      if ($private_ips !== FALSE) {
        $entity->setPrivateIps($private_ips);
      }
      if (isset($instance['PublicIpAddress'])) {
        $public_ip = $instance['PublicIpAddress'];
      }
      if (isset($instance['PublicDnsName'])) {
        $entity->setPublicDns($instance['PublicDnsName']);
      }
      if (isset($instance['PrivateDnsName'])) {
        $entity->setPrivateDns($instance['PrivateDnsName']);
      }

      $entity->setPublicIp($public_ip);
      $entity->setSecurityGroups(implode(', ', $security_groups));
      $entity->setInstanceType($instance['InstanceType']);
      $entity->setRefreshed($timestamp);
      $entity->setLaunchTime(strtotime($instance['LaunchTime']->__toString()));
      $entity->setTerminationTimestamp($termination_timestamp);
      $entity->setTerminationProtection($termination_protection);
      $entity->setUserData($user_data);
      $entity->setSchedule($schedule);
      $entity->setTags($tags);
      $entity->setIamRole($iam_role);
      $entity->setNetworkInterfaces($network_interfaces);
      $entity->setCost($cost);
      $entity->setBlockDevices(implode(', ', $block_devices));
      $uid > 0 ?: $entity->setOwnerById($uid);
      $entity->save();
    }
    else {
      $entity = Instance::create([
        'cloud_context' => $cloud_context,
        'name' => $instanceName ?? $instance['InstanceId'],
        'account_id' => $instance['reservation_ownerid'],
        'security_groups' => implode(', ', $security_groups),
        'instance_id' => $instance['InstanceId'],
        'instance_type' => $instance['InstanceType'],
        'availability_zone' => $instance['Placement']['AvailabilityZone'],
        'tenancy' => $instance['Placement']['Tenancy'],
        'instance_state' => $instance['State']['Name'],
        'public_dns' => $instance['PublicDnsName'],
        'public_ip' => $instance['PublicIpAddress'] ?? NULL,
        'private_dns' => $instance['PrivateDnsName'] ?? NULL,
        'key_pair_name' => $instance['KeyName'],
        'is_monitoring' => $instance['Monitoring']['State'],
        'vpc_id' => $instance['VpcId'] ?? NULL,
        'subnet_id' => $instance['SubnetId'] ?? NULL,
        'source_dest_check' => $instance['SourceDestCheck'] ?? NULL,
        'ebs_optimized' => $instance['EbsOptimized'],
        'root_device_type' => $instance['RootDeviceType'],
        'root_device' => $instance['RootDeviceName'],
        'image_id' => $instance['ImageId'],
        'placement_group' => $instance['Placement']['GroupName'],
        'virtualization' => $instance['VirtualizationType'],
        'reservation' => $instance['reservation_id'],
        'ami_launch_index' => $instance['AmiLaunchIndex'],
        'host_id' => $instance['Placement']['HostId'] ?? NULL,
        'affinity' => $instance['Placement']['Affinity'] ?? NULL,
        'state_transition_reason' => $instance['StateTransitionReason'],
        'instance_lock' => FALSE,
        'launch_time' => strtotime($instance['LaunchTime']->__toString()),
        'created' => strtotime($instance['LaunchTime']->__toString()),
        'changed' => $timestamp,
        'refreshed' => $timestamp,
        'uid' => $uid,
        'termination_timestamp' => $termination_timestamp,
        'termination_protection' => $termination_protection,
        'user_data' => $user_data,
        'schedule' => $schedule,
        'tags' => $tags,
        'iam_role' => $iam_role,
        'cost' => $cost,
        'block_devices' => implode(', ', $block_devices),
      ]);

      if ($private_ips !== FALSE) {
        $entity->setPrivateIps($private_ips);
      }
      $entity->setNetworkInterfaces($network_interfaces);
      $entity->save();
    }
  }

  /**
   * Update or create a image entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $image
   *   The image array.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function updateImage($cloud_context, array $image) {
    /* @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface */
    $ec2Service = \Drupal::service('aws_cloud.ec2');
    $ec2Service->setCloudContext($cloud_context);
    $timestamp = time();

    $cloud_config_plugin = \Drupal::service('plugin.manager.cloud_config_plugin');
    $cloud_config_plugin->setCloudContext($cloud_context);
    $cloud_config = $cloud_config_plugin->loadConfigEntity();

    $block_device_mappings = [];
    foreach ($image['BlockDeviceMappings'] ?: [] as $block_device) {
      $device = [
        'device_name' => $block_device['DeviceName'],
        'virtual_name' => $block_device['VirtualName'] ?? '',
      ];
      if (!empty($block_device['Ebs'])) {
        $device['delete_on_termination'] = $block_device['Ebs']['DeleteOnTermination'] ?? '';
        $device['snapshot_id'] = $block_device['Ebs']['SnapshotId'] ?? '';
        $device['volume_size'] = $block_device['Ebs']['VolumeSize'] ?? '';
        $device['volume_type'] = $block_device['Ebs']['VolumeType'] ?? '';
        $device['encrypted'] = $block_device['Ebs']['Encrypted'] ?? '';
      }
      $block_device_mappings[] = $device;
    }

    $launch_permission_account_ids = [];
    // Call 'describeImageAttribute'
    // only when OwnerId is same as Account ID of Cloud Config.
    // It's to avoid AuthFailure error from AWS API.
    if ($cloud_config->get('field_account_id')->value === $image['OwnerId']) {
      $attribute_result = $ec2Service->describeImageAttribute([
        'ImageId' => $image['ImageId'],
        'Attribute' => 'launchPermission',
      ]);

      foreach ($attribute_result['LaunchPermissions'] ?: [] as $launch_permission) {
        if (!empty($launch_permission['UserId'])) {
          $launch_permission_account_ids[] = $launch_permission['UserId'];
        }
      }
    }

    $name = $ec2Service->getTagName($image, $image['Name']);
    $uid = $ec2Service->getUidTagValue($image, Image::TAG_CREATED_BY_UID);
    $entity_id = $ec2Service->getEntityId('aws_cloud_image', 'image_id', $image['ImageId']);

    // Skip if $entity already exists, by updating 'refreshed' time.
    if (!empty($entity_id)) {
      try {
        $entity = Image::load($entity_id);
        $entity->setName($name);
        $entity->setRefreshed($timestamp);
        $entity->setVisibility($image['Public']);
        $entity->setStatus($image['State']);
        $entity->setBlockDeviceMappings($block_device_mappings);
        $entity->setLaunchPermissionAccountIds($launch_permission_account_ids);
        $uid > 0 ?: $entity->setOwnerById($uid);
        $entity->save();
      }
      catch (\Exception $e) {
        \Drupal::service('cloud')->handleException($e);
      }
    }
    else {

      $entity = Image::create([
        'cloud_context' => $cloud_context,
        'image_id' => $image['ImageId'],
        'account_id' => $image['OwnerId'],
        'architecture' => $image['Architecture'] ?? '',
        'virtualization_type' => $image['VirtualizationType'] ?? '',
        'root_device_type' => $image['RootDeviceType'] ?? '',
        'root_device_name' => $image['RootDeviceName'] ?? '',
        'ami_name' => $image['Name'],
        'name' => $image['Name'],
        'kernel_id' => $image['KernelId'] ?? '',
        'ramdisk_id' => $image['RamdiskId'] ?? '',
        'image_type' => $image['ImageType'],
        'product_code' => isset($image['ProductCodes']) ? implode(',', array_column($image['ProductCodes'], 'ProductCode')) : '',
        'source' => $image['ImageLocation'] ?? '',
        'state_reason' => isset($image['StateReason']) ? $image['StateReason']['Message'] : '',
        'platform' => $image['Platform'] ?? '',
        'description' => $image['Description'] ?? '',
        'visibility' => $image['Public'],
        'block_device_mappings' => $block_device_mappings,
        'launch_permission_account_ids' => $launch_permission_account_ids,
        'status' => $image['State'],
        'created' => strtotime($image['CreationDate'] ?? ''),
        'changed' => $timestamp,
        'refreshed' => $timestamp,
        'uid' => $uid,
      ]);

      $entity->save();
    }
  }

  /**
   * Update or create a security group entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $security_group
   *   The security_group array.
   */
  public static function updateSecurityGroup($cloud_context, array $security_group) {
    /* @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface */
    $ec2Service = \Drupal::service('aws_cloud.ec2');
    $ec2Service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $ec2Service->getTagName($security_group, $security_group['GroupName']);
    $uid = $ec2Service->getUidTagValue($security_group, SecurityGroup::TAG_CREATED_BY_UID);
    $entity_id = $ec2Service->getEntityId('aws_cloud_security_group', 'group_id', $security_group['GroupId']);

    // Skip if $entity already exists, by updating 'refreshed' time.
    if (!empty($entity_id)) {
      /* @var \Drupal\aws_cloud\Entity\Ec2\SecurityGroup $entity */
      try {
        $entity = SecurityGroup::load($entity_id);
        $entity->setName($name);
        $entity->setRefreshed($timestamp);
        $uid ?: $entity->setOwnerById($uid);
      }
      catch (\Exception $e) {
        \Drupal::service('cloud')->handleException($e);
      }
    }
    else {
      // Create a brand new SecurityGroup entity.
      $entity = SecurityGroup::create([
        'cloud_context' => $cloud_context,
        'name' => $security_group['GroupName'] ?? $security_group['GroupId'],
        'group_id' => $security_group['GroupId'],
        'group_name' => $security_group['GroupName'],
        'description' => $security_group['Description'],
        'vpc_id' => $security_group['VpcId'] ?? NULL,
        'account_id' => $security_group['OwnerId'],
        'created' => $timestamp,
        'changed' => $timestamp,
        'refreshed' => $timestamp,
        'uid' => $uid,
      ]);
    }

    if (isset($security_group['VpcId']) && !empty($security_group['VpcId'])) {
      // Check if VPC is default.  This involves another API call.
      $vpcs = $ec2Service->describeVpcs([
        'VpcIds' => [$security_group['VpcId']],
      ]);
      if ($vpcs['Vpcs']) {
        $default = $vpcs['Vpcs'][0]['IsDefault'];
        $entity->setDefaultVpc($default);
      }
    }

    // Setup the Inbound permissions.
    if (isset($security_group['IpPermissions'])) {
      $ec2Service->setupIpPermissions($entity, 'ip_permission', $security_group['IpPermissions']);
    }

    // Setup outbound permissions.
    if (isset($security_group['VpcId']) && isset($security_group['IpPermissionsEgress'])) {
      $ec2Service->setupIpPermissions($entity, 'outbound_permission', $security_group['IpPermissionsEgress']);
    }
    $entity->save();
  }

  /**
   * Update or create a network interface entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $network_interface
   *   The network interface array.
   */
  public static function updateNetworkInterface($cloud_context, array $network_interface) {
    /* @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface */
    $ec2Service = \Drupal::service('aws_cloud.ec2');
    $ec2Service->setCloudContext($cloud_context);
    $timestamp = time();

    // Set up the primary and secondary private IP addresses.
    // Setup the allocation_ids.  The allocation_ids are used during Elastic
    // IP assignment.
    $primary_private_ip = NULL;
    $secondary_private_ip = NULL;
    $primary_association_id = NULL;
    $secondary_association_id = NULL;
    $public_ips = NULL;

    foreach ($network_interface['PrivateIpAddresses'] ?: [] as $private_ip_address) {
      if ($private_ip_address['Primary'] === TRUE) {
        $primary_private_ip = $private_ip_address['PrivateIpAddress'];
        if (isset($private_ip_address['Association'])) {
          if (!empty($private_ip_address['Association']['AssociationId'])) {
            $primary_association_id = $private_ip_address['Association']['AssociationId'];
          }
          if (!empty($private_ip_address['Association']['PublicIp'])) {
            $public_ips[] = $private_ip_address['Association']['PublicIp'];
          }
        }
      }
      else {
        $secondary_private_ip = $private_ip_address['PrivateIpAddress'];
        if (isset($private_ip_address['Association'])) {
          if (!empty($private_ip_address['Association']['AssociationId'])) {
            $secondary_association_id = $private_ip_address['Association']['AssociationId'];
          }
          if (!empty($private_ip_address['Association']['PublicIp'])) {
            $public_ips[] = $private_ip_address['Association']['PublicIp'];
          }
        }
      }
    }

    $security_groups = [];
    foreach ($network_interface['Groups'] ?: [] as $security_group) {
      $security_groups[] = $security_group['GroupName'];
    }

    // The tag key of the network interface is 'TagSet'.
    // So changing the key to align to other entities.
    // If this key changes to 'Tags' on AWS API, this block needs to be deleted.
    if (isset($network_interface['TagSet'])) {
      $network_interface['Tags'] = $network_interface['TagSet'];
    }

    $name = $ec2Service->getTagName($network_interface, $network_interface['NetworkInterfaceId']);
    $uid = $ec2Service->getUidTagValue($network_interface, NetworkInterface::TAG_CREATED_BY_UID);
    $entity_id = $ec2Service->getEntityId('aws_cloud_network_interface', 'network_interface_id', $network_interface['NetworkInterfaceId']);

    $allocation_id = NULL;
    if (!empty($network_interface['Association'])) {
      $allocation_id = $network_interface['Association']['AllocationId'] ?? NULL;
    }

    $attachment_id = NULL;
    $attachment_owner = NULL;
    $attachment_status = NULL;
    $instance_id = NULL;
    $device_index = NULL;
    $delete_on_termination = NULL;
    if (!empty($network_interface['Attachment'])) {
      $attachment_id = $network_interface['Attachment']['AttachmentId'] ?? NULL;
      $attachment_owner = $network_interface['Attachment']['InstanceOwnerId'] ?? NULL;
      $attachment_status = $network_interface['Attachment']['Status'] ?? NULL;
      $instance_id = $network_interface['Attachment']['InstanceId'] ?? NULL;
      $device_index = $network_interface['Attachment']['DeviceIndex'] ?? NULL;
      $delete_on_termination = $network_interface['Attachment']['DeleteOnTermination'] ?? NULL;
    }

    $network_vpc_id = $network_interface['VpcId'] ?? NULL;

    // Skip if $entity already exists, by updating 'refreshed' time.
    if (!empty($entity_id)) {
      /* @var \Drupal\aws_cloud\Entity\Ec2\NetworkInterface $entity */
      $entity = NetworkInterface::load($entity_id);
      $entity->setName($name);
      $entity->setRefreshed($timestamp);
      $entity->setPrimaryPrivateIp($primary_private_ip);
      $entity->setSecondaryPrivateIp($secondary_private_ip);
      $entity->setAssociationId($primary_association_id);
      $entity->setSecondaryAssociationId($secondary_association_id);
      if ($public_ips !== NULL) {
        $public_ips = implode(', ', $public_ips);
      }

      $entity->setPublicIps($public_ips);
      $entity->setVpcId($network_vpc_id);
      $entity->setStatus($network_interface['Status']);
      $uid > 0 ?: $entity->setOwnerById($uid);
      $entity->save();
    }
    else {
      $entity = NetworkInterface::create([
        'cloud_context' => $cloud_context,
        'name' => $network_interface['NetworkInterfaceId'],
        'network_interface_id' => $network_interface['NetworkInterfaceId'],
        'vpc_id' => $network_vpc_id,
        'mac_address' => $network_interface['MacAddress'],
        'security_groups' => implode(', ', $security_groups),
        'status' => $network_interface['Status'],
        'private_dns' => $network_interface['PrivateDnsName'] ?? NULL,
        'primary_private_ip' => $primary_private_ip,
        'secondary_private_ips' => $secondary_private_ip,
        'attachment_id' => $attachment_id,
        'attachment_owner' => $attachment_owner,
        'attachment_status' => $attachment_status,
        'account_id' => $network_interface['OwnerId'],
        'association_id' => $primary_association_id,
        'secondary_association_id' => $secondary_association_id,
        'subnet_id' => $network_interface['SubnetId'] ?? NULL,
        'description' => $network_interface['Description'],
        'public_ips' => $public_ips,
        'source_dest_check' => $network_interface['SourceDestCheck'] ?? NULL,
        'instance_id' => $instance_id,
        'device_index' => $device_index,
        'delete_on_termination' => $delete_on_termination,
        'allocation_id' => $allocation_id,
        'created' => $timestamp,
        'changed' => $timestamp,
        'refreshed' => $timestamp,
        'uid' => $uid,
      ]);
      $entity->save();
    }
  }

  /**
   * Update or create an Elastic IP entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $elastic_ip
   *   The Elastic IP array.
   */
  public static function updateElasticIp($cloud_context, array $elastic_ip) {
    /* @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface */
    $ec2Service = \Drupal::service('aws_cloud.ec2');
    $ec2Service->setCloudContext($cloud_context);
    $timestamp = time();

    $name = $ec2Service->getTagName($elastic_ip, $elastic_ip['PublicIp']);
    $uid = $ec2Service->getUidTagValue($elastic_ip, ElasticIp::TAG_CREATED_BY_UID);
    $entity_id = $ec2Service->getEntityId('aws_cloud_elastic_ip', 'public_ip', $elastic_ip['PublicIp']);

    // Skip if $entity already exists, by updating 'refreshed' time.
    if (!empty($entity_id)) {
      $entity = ElasticIp::load($entity_id);

      // Update fields.
      try {
        $entity->setName($name);
        $entity->setInstanceId($elastic_ip['InstanceId'] ?? '');
        $entity->setNetworkInterfaceId($elastic_ip['NetworkInterfaceId'] ?? '');
        $entity->setPrivateIpAddress($elastic_ip['PrivateIpAddress'] ?? '');
        $entity->setNetworkInterfaceOwner($elastic_ip['NetworkInterfaceOwnerId'] ?? '');
        $entity->setAllocationId($elastic_ip['AllocationId'] ?? '');
        $entity->setAssociationId($elastic_ip['AssociationId'] ?? '');
        $entity->setDomain($elastic_ip['Domain'] ?? '');

        $entity->setRefreshed($timestamp);
        $uid > 0 ?: $entity->setOwnerById($uid);
        $entity->save();
      }
      catch (\Exception $e) {
        \Drupal::service('cloud')->handleException($e);
      }
    }
    else {
      $entity = ElasticIp::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'public_ip' => $elastic_ip['PublicIp'],
        'instance_id' => $elastic_ip['InstanceId'] ?? '',
        'network_interface_id' => $elastic_ip['NetworkInterfaceId'] ?? '',
        'private_ip_address' => $elastic_ip['PrivateIpAddress'] ?? '',
        'network_interface_owner' => $elastic_ip['NetworkInterfaceOwnerId'] ?? '',
        'allocation_id' => $elastic_ip['AllocationId'] ?? '',
        'association_id' => $elastic_ip['AssociationId'] ?? '',
        'domain' => $elastic_ip['Domain'] ?? '',
        'created' => $timestamp,
        'changed' => $timestamp,
        'refreshed' => $timestamp,
        'uid' => $uid,
      ]);
      $entity->save();
    }
  }

  /**
   * Update or create a key pair entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $key_pair
   *   The key_pair array.
   */
  public static function updateKeyPair($cloud_context, array $key_pair) {
    /* @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface */
    $ec2Service = \Drupal::service('aws_cloud.ec2');
    $ec2Service->setCloudContext($cloud_context);
    $timestamp = time();

    $entity_id = $ec2Service->getEntityId('aws_cloud_key_pair', 'key_pair_name', $key_pair['KeyName']);

    if (!empty($entity_id)) {
      $entity = KeyPair::load($entity_id);
      if (!empty($entity)) {
        $entity->setRefreshed($timestamp);
        $entity->save();
      }
    }
    else {
      $entity = KeyPair::create([
        'cloud_context' => $cloud_context,
        'key_pair_name' => $key_pair['KeyName'],
        'key_fingerprint' => $key_pair['KeyFingerprint'],
        'created' => $timestamp,
        'changed' => $timestamp,
        'refreshed' => $timestamp,
      ]);
      $entity->save();
    }
  }

  /**
   * Update or create a snapshot entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $snapshot
   *   The snapshot array.
   */
  public static function updateSnapshot($cloud_context, array $snapshot) {
    /* @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface */
    $ec2Service = \Drupal::service('aws_cloud.ec2');
    $ec2Service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $ec2Service->getTagName($snapshot, $snapshot['SnapshotId']);
    $entity_id = $ec2Service->getEntityId('aws_cloud_snapshot', 'snapshot_id', $snapshot['SnapshotId']);
    $uid = $ec2Service->getUidTagValue($snapshot, Snapshot::TAG_CREATED_BY_UID);

    if (!empty($entity_id)) {
      $entity = Snapshot::load($entity_id);
      if (!empty($entity)) {
        $entity->setName($name);
        $entity->setStatus($snapshot['State']);
        $entity->setSize($snapshot['VolumeSize']);
        $entity->setRefreshed($timestamp);
        $uid > 0 ?: $entity->setOwnerById($uid);
        $entity->setCreated(strtotime($snapshot['StartTime']));
        $entity->save();
      }
    }
    else {
      $entity = Snapshot::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'snapshot_id' => $snapshot['SnapshotId'],
        'size' => $snapshot['VolumeSize'],
        'description' => $snapshot['Description'],
        'status' => $snapshot['State'],
        'volume_id' => $snapshot['VolumeId'],
        'progress' => $snapshot['Progress'],
        'encrypted' => $snapshot['Encrypted'] === FALSE ? 'Not Encrypted' : 'Encrypted',
        'kms_key_id' => $snapshot['KmsKeyId'] ?? NULL,
        'account_id' => $snapshot['OwnerId'],
        'owner_aliases' => $snapshot['OwnerAlias'] ?? NULL,
        'state_message' => $snapshot['StateMessage'] ?? NULL,
        'created' => strtotime($snapshot['StartTime']),
        'changed' => $timestamp,
        'refreshed' => $timestamp,
        'uid' => $uid,
      ]);
      $entity->save();
    }
  }

  /**
   * Update or create a VPC entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $vpc
   *   The VPC array.
   */
  public static function updateVpc($cloud_context, array $vpc) {
    /* @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface */
    $ec2Service = \Drupal::service('aws_cloud.ec2');
    $ec2Service->setCloudContext($cloud_context);

    $vpc_id = $vpc['VpcId'] ?? NULL;

    $timestamp = time();
    $name = $ec2Service->getTagName($vpc, $vpc_id);
    $entity_id = $ec2Service->getEntityId('aws_cloud_vpc', 'vpc_id', $vpc_id);
    $uid = $ec2Service->getUidTagValue($vpc, Vpc::TAG_CREATED_BY_UID);

    // Tags.
    $tags = [];
    if (!isset($vpc['Tags'])) {
      $vpc['Tags'] = [];
    }
    foreach ($vpc['Tags'] ?: [] as $tag) {
      $tags[] = ['item_key' => $tag['Key'], 'item_value' => $tag['Value']];
    }

    usort($tags, static function ($a, $b) {
      if ($a['item_key'] === 'Name') {
        return -1;
      }

      if ($b['item_key'] === 'Name') {
        return 1;
      }

      return strcmp($a['item_key'], $b['item_key']);
    });

    // CIDR blocks.
    $cidr_blocks = [];
    if (!isset($vpc['CidrBlockAssociationSet'])) {
      $vpc['CidrBlockAssociationSet'] = [];
    }
    foreach ($vpc['CidrBlockAssociationSet'] ?: [] as $cidr_block) {
      if ($cidr_block['CidrBlockState']['State'] !== 'associated') {
        continue;
      }

      $cidr_blocks[] = [
        'cidr' => $cidr_block['CidrBlock'],
        'state' => $cidr_block['CidrBlockState']['State'],
        'status_message' => isset($cidr_block['CidrBlockState']['StatusMessage'])
        ? $cidr_block['CidrBlock']['Status_message']
        : '',
        'association_id' => $cidr_block['AssociationId'],
      ];
    }

    // IPv6 CIDR blocks.
    $ipv6_cidr_blocks = [];
    if (!isset($vpc['Ipv6CidrBlockAssociationSet'])) {
      $vpc['Ipv6CidrBlockAssociationSet'] = [];
    }
    foreach ($vpc['Ipv6CidrBlockAssociationSet'] ?: [] as $ipv6_cidr_block) {
      if ($ipv6_cidr_block['Ipv6CidrBlockState']['State'] !== 'associated') {
        continue;
      }

      $ipv6_cidr_blocks[] = [
        'cidr' => $ipv6_cidr_block['Ipv6CidrBlock'],
        'state' => $ipv6_cidr_block['Ipv6CidrBlockState']['State'],
        'status_message' => isset($ipv6_cidr_block['Ipv6CidrBlockState']['StatusMessage'])
        ? $ipv6_cidr_block['CidrBlock']['Status_message']
        : '',
        'association_id' => $ipv6_cidr_block['AssociationId'],
      ];
    }

    if (!empty($entity_id)) {
      $entity = Vpc::load($entity_id);
      if (!empty($entity)) {
        $entity->setName($name);
        $entity->setRefreshed($timestamp);
        $uid > 0 ?: $entity->setOwnerById($uid);
      }
    }
    else {
      $entity = Vpc::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => $timestamp,
        'changed' => $timestamp,
        'refreshed' => $timestamp,
        'uid' => $uid,
      ]);
    }

    $entity->setCidrBlock($vpc['CidrBlock']);
    $entity->setDhcpOptionsId($vpc['DhcpOptionsId']);
    $entity->setInstanceTenancy($vpc['InstanceTenancy']);
    $entity->setDefault($vpc['IsDefault']);
    $entity->setAccountId($vpc['OwnerId'] ?? NULL);
    $entity->setState($vpc['State']);
    $entity->setVpcId($vpc_id);
    $entity->setTags($tags);
    $entity->setCidrBlocks($cidr_blocks);
    $entity->setIpv6CidrBlocks($ipv6_cidr_blocks);
    $entity->save();
  }

  /**
   * Update or create a VPC Peering Connection entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $vpc_peering_connection
   *   The VPC Peering Connection array.
   */
  public static function updateVpcPeeringConnection($cloud_context, array $vpc_peering_connection) {
    /* @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface */
    $ec2Service = \Drupal::service('aws_cloud.ec2');
    $ec2Service->setCloudContext($cloud_context);

    $vpc_peering_connection_id = $vpc_peering_connection['VpcPeeringConnectionId'] ?? NULL;

    $timestamp = time();
    $name = $ec2Service->getTagName($vpc_peering_connection, $vpc_peering_connection_id);
    $entity_id = $ec2Service->getEntityId('aws_cloud_vpc_peering_connection', 'vpc_peering_connection_id', $vpc_peering_connection_id);
    $uid = $ec2Service->getUidTagValue($vpc_peering_connection, VpcPeeringConnection::TAG_CREATED_BY_UID);

    // Tags.
    $tags = [];
    if (!isset($vpc_peering_connection['Tags'])) {
      $vpc_peering_connection['Tags'] = [];
    }
    foreach ($vpc_peering_connection['Tags'] ?: [] as $tag) {
      $tags[] = ['item_key' => $tag['Key'], 'item_value' => $tag['Value']];
    }

    usort($tags, static function ($a, $b) {
      if ($a['item_key'] === 'Name') {
        return -1;
      }

      if ($b['item_key'] === 'Name') {
        return 1;
      }

      return strcmp($a['item_key'], $b['item_key']);
    });

    if (!empty($entity_id)) {
      $entity = VpcPeeringConnection::load($entity_id);
      if (!empty($entity)) {
        $entity->setName($name);
        $entity->setRefreshed($timestamp);
        $uid > 0 ?: $entity->setOwnerById($uid);
      }
    }
    else {
      $entity = VpcPeeringConnection::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => $timestamp,
        'changed' => $timestamp,
        'refreshed' => $timestamp,
        'uid' => $uid,
      ]);
    }

    $entity->setVpcPeeringConnectionId($vpc_peering_connection_id);

    $entity->setRequesterAccountId($vpc_peering_connection['RequesterVpcInfo']['OwnerId']);
    $entity->setRequesterVpcId($vpc_peering_connection['RequesterVpcInfo']['VpcId']);
    $entity->setRequesterCidrBlock($vpc_peering_connection['RequesterVpcInfo']['CidrBlock'] ?? '');
    $entity->setRequesterRegion($vpc_peering_connection['RequesterVpcInfo']['Region']);

    $entity->setAccepterAccountId($vpc_peering_connection['AccepterVpcInfo']['OwnerId']);
    $entity->setAccepterVpcId($vpc_peering_connection['AccepterVpcInfo']['VpcId']);
    $entity->setAccepterCidrBlock($vpc_peering_connection['AccepterVpcInfo']['CidrBlock'] ?? '');
    $entity->setAccepterRegion($vpc_peering_connection['AccepterVpcInfo']['Region']);

    $entity->setStatusCode($vpc_peering_connection['Status']['Code']);
    $entity->setStatusMessage($vpc_peering_connection['Status']['Message']);
    if (!empty($vpc_peering_connection['ExpirationTime'])) {
      $entity->setExpirationTime(strtotime($vpc_peering_connection['ExpirationTime']->__toString()));
    }

    $entity->setTags($tags);
    $entity->save();
  }

  /**
   * Update or create a subnet entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $subnet
   *   The subnet array.
   */
  public static function updateSubnet($cloud_context, array $subnet) {
    /* @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface */
    $ec2Service = \Drupal::service('aws_cloud.ec2');
    $ec2Service->setCloudContext($cloud_context);

    $subnet_id = $subnet['SubnetId'] ?? NULL;

    $timestamp = time();
    $name = $ec2Service->getTagName($subnet, $subnet_id);
    $entity_id = $ec2Service->getEntityId('aws_cloud_subnet', 'subnet_id', $subnet_id);
    $uid = $ec2Service->getUidTagValue($subnet, Subnet::TAG_CREATED_BY_UID);

    $subnet_vpc_id = $subnet['VpcId'] ?? NULL;

    // Tags.
    $tags = [];
    if (!isset($subnet['Tags'])) {
      $subnet['Tags'] = [];
    }
    foreach ($subnet['Tags'] ?: [] as $tag) {
      $tags[] = ['item_key' => $tag['Key'], 'item_value' => $tag['Value']];
    }

    usort($tags, static function ($a, $b) {
      if ($a['item_key'] === 'Name') {
        return -1;
      }

      if ($b['item_key'] === 'Name') {
        return 1;
      }

      return strcmp($a['item_key'], $b['item_key']);
    });

    if (!empty($entity_id)) {
      $entity = Subnet::load($entity_id);
      if (!empty($entity)) {
        $entity->setName($name);
        $entity->setRefreshed($timestamp);
        $uid > 0 ?: $entity->setOwnerById($uid);
      }
    }
    else {
      $entity = Subnet::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => $timestamp,
        'changed' => $timestamp,
        'refreshed' => $timestamp,
        'uid' => $uid,
      ]);
    }

    $entity->setCidrBlock($subnet['CidrBlock']);
    $entity->setAccountId($subnet['OwnerId'] ?? NULL);
    $entity->setState($subnet['State']);
    $entity->setSubnetId($subnet_id);
    $entity->setVpcId($subnet_vpc_id);
    $entity->setTags($tags);
    $entity->save();
  }

  /**
   * Update or create a volume entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $volume
   *   The volume array.
   * @param array $snapshot_id_name_map
   *   The snapshot map.
   */
  public static function updateVolume($cloud_context, array $volume, array $snapshot_id_name_map) {
    /* @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface */
    $ec2Service = \Drupal::service('aws_cloud.ec2');
    $ec2Service->setCloudContext($cloud_context);

    $timestamp = time();

    $attachments = [];
    foreach ($volume['Attachments'] ?: [] as $attachment) {
      $attachments[] = $attachment['InstanceId'];
    }

    $name = $ec2Service->getTagName($volume, $volume['VolumeId']);
    $entity_id = $ec2Service->getEntityId('aws_cloud_volume', 'volume_id', $volume['VolumeId']);
    $uid = $ec2Service->getUidTagValue($volume, Volume::TAG_CREATED_BY_UID);

    if ($uid === 0) {
      // Inherit the volume uid from the instance that launched it.
      if (count($attachments)) {
        $uid = $ec2Service->getInstanceUid($attachments[0]);
      }
    }

    $volume_iops = $volume['Iops'] ?? NULL;

    // Skip if $entity already exists, by updating 'refreshed' time.
    if (!empty($entity_id)) {
      /* @var \Drupal\aws_cloud\Entity\Ec2\Volume $entity */
      $entity = Volume::load($entity_id);
      $entity->setName($name);
      $entity->setRefreshed($timestamp);
      $entity->setState($volume['State']);
      $entity->setAttachmentInformation(implode(', ', $attachments));
      $entity->setCreated(strtotime($volume['CreateTime']));
      $entity->setSnapshotId($volume['SnapshotId']);
      $entity->setSnapshotName(empty($volume['SnapshotId'])
        ? ''
        : $snapshot_id_name_map[$volume['SnapshotId']]);
      $entity->setSize($volume['Size']);
      $entity->setVolumeType($volume['VolumeType']);
      $entity->setIops($volume_iops);

      $uid > 0 ?: $entity->setOwnerById($uid);
      $entity->save();
    }
    else {
      $entity = Volume::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'volume_id' => $volume['VolumeId'],
        'size' => $volume['Size'],
        'state' => $volume['State'],
        'volume_status' => $volume['VirtualizationType'] ?? NULL,
        'attachment_information' => implode(', ', $attachments),
        'volume_type' => $volume['VolumeType'],
        'iops' => $volume_iops,
        'snapshot_id' => $volume['SnapshotId'],
        'snapshot_name' => empty($volume['SnapshotId']) ? '' : $snapshot_id_name_map[$volume['SnapshotId']],
        'availability_zone' => $volume['AvailabilityZone'],
        'encrypted' => $volume['Encrypted'],
        'kms_key_id' => $volume['KmsKeyId'] ?? NULL,
        'created' => strtotime($volume['CreateTime']),
        'changed' => $timestamp,
        'refreshed' => $timestamp,
        'uid' => $uid,
      ]);
      $entity->save();
    }
  }

  /**
   * Update or create a template entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $template
   *   The launch template array.
   */
  public static function updateCloudServerTemplate($cloud_context, array $template) {
    /* @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface */
    $ec2_service = \Drupal::service('aws_cloud.ec2');
    $ec2_service->setCloudContext($cloud_context);
    $timestamp = time();

    $entity_id = $ec2_service->getEntityId(
      'cloud_server_template',
      'name',
      $template['LaunchTemplateName'],
      ['type' => 'aws_cloud']
    );

    // Skip if $entity already exists, by updating 'refreshed' time.
    if (!empty($entity_id)) {
      $entity = CloudServerTemplate::load($entity_id);
    }
    else {
      $entity = CloudServerTemplate::create([
        'cloud_context' => $cloud_context,
        'type' => 'aws_cloud',
        'name' => $template['LaunchTemplateName'],
        'created' => strtotime($template['CreateTime']),
        'changed' => $timestamp,
      ]);
      $entity->save();
    }

    $entity_type_manager = \Drupal::entityTypeManager();
    $entity_storage = $entity_type_manager->getStorage('cloud_server_template');
    $revision_ids = $entity_storage->revisionIds($entity);

    // Get template data.
    $result = $ec2_service->describeLaunchTemplateVersions([
      'LaunchTemplateName' => $template['LaunchTemplateName'],
    ]);
    $versions = array_reverse($result['LaunchTemplateVersions'] ?: []);

    // Update revisions.
    $revision_ids_updated = [];
    foreach ($revision_ids ?: [] as $revision_id) {
      $version = array_shift($versions);
      if ($version === NULL) {
        break;
      }

      $revision = $entity_storage->loadRevision($revision_id);
      !empty($revision) ?: $revision->isDefaultRevision(TRUE);
      self::updateCloudServerTemplateRevision($ec2_service, $revision, $version);
      $revision_ids_updated[] = $revision_id;
    }

    // Remove revisions not updated.
    $revision_ids_not_updated = array_diff($revision_ids, $revision_ids_updated);
    foreach ($revision_ids_not_updated ?: [] as $revision_id) {
      $entity_storage->deleteRevision($revision_id);
    }

    // Add revisions if there are versions left.
    foreach ($versions ?: [] as $version) {
      // Create a new revision.
      $revision = $entity;
      $revision->setNewRevision();
      !empty($revision) ?: $revision->isDefaultRevision(TRUE);

      self::updateCloudServerTemplateRevision($ec2_service, $revision, $version);
    }
  }

  /**
   * Update the cloud server template revision.
   *
   * @param \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface $ec2_service
   *   AWS Cloud EC2 Service.
   * @param \Drupal\cloud\Entity\CloudServerTemplate $revision
   *   The revision of a cloud server template.
   * @param array $version
   *   The array of a AWS launch template version.
   */
  private static function updateCloudServerTemplateRevision(
    Ec2ServiceInterface $ec2_service,
    CloudServerTemplate $revision,
    array $version
  ) {
    $revision->set('field_version', $version['VersionNumber']);
    $revision->setRevisionLogMessage($version['VersionDescription'] ?? '');

    $template_data = $version['LaunchTemplateData'];

    $revision->set('field_image_id', $template_data['ImageId'] ?? '');
    $revision->set('field_instance_type', $template_data['InstanceType'] ?? '');
    $revision->set('field_iam_role', $template_data['IamInstanceProfile']['Arn'] ?? '');
    $revision->set('field_kernel_id', $template_data['KernelId'] ?? '');
    $revision->set('field_ram', $template_data['RamdiskId'] ?? '');
    $revision->set('field_instance_shutdown_behavior', $template_data['InstanceInitiatedShutdownBehavior'] ?? '');
    $revision->set('field_termination_protection', empty($template_data['DisableApiTermination']) ? '0' : '1');
    $revision->set('field_monitoring', (isset($template_data['Monitoring']) && empty($template_data['Monitoring']['Enabled'])) ? '0' : '1');
    $revision->set('field_user_data', $template_data['UserData'] ?? '');

    // Security groups.
    $security_group_entity_ids = [];
    if (!empty($template_data['SecurityGroupIds'])) {
      foreach ($template_data['SecurityGroupIds'] ?: [] as $group_id) {
        $entity_id = $ec2_service->getEntityId(
          'aws_cloud_security_group',
          'group_id',
          $group_id
        );

        if ($entity_id !== NULL) {
          $security_group_entity_ids[] = $entity_id;
        }
      }
    }
    $revision->set('field_security_group', $security_group_entity_ids);

    // Key pair.
    $key_pair_id = NULL;
    if (isset($template_data['KeyName'])) {
      $key_pair_id = $ec2_service->getEntityId(
        'aws_cloud_key_pair',
        'key_pair_name',
        $template_data['KeyName']
      );
    }
    $revision->set('field_ssh_key', $key_pair_id);

    // Network interface.
    $network_interface_id = NULL;
    if (!empty($template_data['NetworkInterfaces'])) {
      $network_interfaces = $template_data['NetworkInterfaces'];
      $network_interface = array_shift($network_interfaces);

      if (!empty($network_interface['NetworkInterfaceId'])) {
        $network_interface_id = $ec2_service->getEntityId(
          'aws_cloud_network_interface',
          'network_interface_id',
          $network_interface['NetworkInterfaceId']
        );
      }
    }
    $revision->set('field_network', $network_interface_id);

    // Tags.
    $tags = [];
    if (!empty($template_data['TagSpecifications'])
      && !empty($template_data['TagSpecifications'][0]['Tags'])) {

      $tags = $template_data['TagSpecifications'][0]['Tags'];
    }

    $uid = 0;
    foreach ($tags ?: [] as $tag) {
      $name = $tag['Key'];
      $value = $tag['Value'];
      if (strpos($name, 'cloud_server_template') !== 0) {
        continue;
      }

      if ($name === CloudServerTemplate::TAG_CREATED_BY_UID) {
        $uid = $value;
        continue;
      }

      $field_name = 'field_' . substr($name, strlen('cloud_server_template_'));
      if ($revision->hasField($field_name)) {
        $revision->set($field_name, $value);
      }
    }

    $uid > 0 ?: $revision->setOwnerId($uid);

    // Update field_tags.
    $field_tags = [];
    foreach ($tags ?: [] as $tag) {
      $field_tags[] = ['item_key' => $tag['Key'], 'item_value' => $tag['Value']];
    }
    usort($field_tags, static function ($a, $b) {
      if (strpos($a['item_key'], 'cloud_server_template_') === 0) {
        if (strpos($b['item_key'], 'cloud_server_template_') === 0) {
          return strcmp($a['item_key'], $b['item_key']);
        }
        else {
          return -1;
        }
      }
      else {
        if (strpos($b['item_key'], 'cloud_server_template_') === 0) {
          return 1;
        }
        else {
          return strcmp($a['item_key'], $b['item_key']);
        }
      }
    });

    $revision->set('field_tags', $field_tags);
    $revision->setRevisionCreationTime(strtotime($version['CreateTime']));

    $revision->save();
  }

}
