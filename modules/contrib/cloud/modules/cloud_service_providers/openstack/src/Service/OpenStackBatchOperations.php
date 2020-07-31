<?php

namespace Drupal\openstack\Service;

use Drupal\openstack\Entity\OpenStackInstance;
use Drupal\openstack\Entity\OpenStackImage;
use Drupal\openstack\Entity\OpenStackKeyPair;
use Drupal\openstack\Entity\OpenStackSecurityGroup;
use Drupal\openstack\Entity\OpenStackVolume;
use Drupal\openstack\Entity\OpenStackSnapshot;
use Drupal\openstack\Entity\OpenStackNetworkInterface;
use Drupal\openstack\Entity\OpenStackFloatingIp;

/**
 * Entity update methods for Batch API processing.
 */
class OpenStackBatchOperations {

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
    /* @var \Drupal\openstack\Service\OpenStackEc2Service $openStackEc2Service */
    $openStackEc2Service = \Drupal::service('openstack.ec2');
    $openStackEc2Service->setCloudContext($cloud_context);
    $timestamp = time();

    $config_factory = \Drupal::configFactory();

    // Get volumeIDs associated to instances.
    $block_devices = [];
    if (isset($instance['BlockDeviceMappings'])) {
      foreach ($instance['BlockDeviceMappings'] ?: [] as $block_device) {
        $block_devices[] = $block_device['Ebs']['VolumeId'];
      }
    }

    $instance_name = '';
    $uid = 0;
    $termination_timestamp = NULL;
    $tags = [];
    if (!isset($instance['Tags'])) {
      $instance['Tags'] = [];
    }
    foreach ($instance['Tags'] ?: [] as $tag) {
      if ($tag['Key'] === 'Name') {
        $instance_name = $tag['Value'];
      }
      if ($tag['Key'] === 'openstack_' . OpenStackInstance::TAG_LAUNCHED_BY_UID) {
        $uid = $tag['Value'];
      }
      if ($tag['Key'] === 'openstack_' . OpenStackInstance::TAG_TERMINATION_TIMESTAMP) {
        if ($tag['Value'] !== '') {
          $termination_timestamp = (int) $tag['Value'];
        }
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
    if (empty($instance_name)) {
      $instance_name = $instance['InstanceId'];
    }

    $security_groups = [];
    foreach ($instance['SecurityGroups'] ?: [] as $security_group) {
      $security_groups[] = $security_group['GroupName'];
    }

    // Termination protection.
    $attribute_result = $openStackEc2Service->describeInstanceAttribute([
      'InstanceId' => $instance['InstanceId'],
      'Attribute' => 'disableApiTermination',
    ]);
    $termination_protection = $attribute_result['DisableApiTermination']['Value'];

    // Get user data.
    $attribute_result = $openStackEc2Service->describeInstanceAttribute([
      'InstanceId' => $instance['InstanceId'],
      'Attribute' => 'userData',
    ]);
    $user_data = '';
    if (!empty($attribute_result['UserData']['Value'])) {
      $user_data = base64_decode($attribute_result['UserData']['Value']);
    }

    // Use NetworkInterface to look up private IPs.  In EC2-VPC,
    // an instance can have more than one private IP.
    $network_interfaces = [];
    $private_ips = FALSE;

    if (isset($instance['NetworkInterfaces'])) {
      $private_ips = $openStackEc2Service->getPrivateIps($instance['NetworkInterfaces']);
      foreach ($instance['NetworkInterfaces'] ?: [] as $interface) {
        $network_interfaces[] = $interface['NetworkInterfaceId'];
      }
    }

    $entity_id = $openStackEc2Service->getEntityId('openstack_instance', 'instance_id', $instance['InstanceId']);
    // Skip if $entity already exists, by updating 'refreshed' time.
    if (!empty($entity_id)) {
      /* @var \Drupal\openstack\Service\OpenStackEc2Service $openStackEc2Service */
      $entity = OpenStackInstance::load($entity_id);
      $entity->setName($instance_name);
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
      $entity->setTags($tags);
      $entity->setNetworkInterfaces($network_interfaces);
      $entity->setBlockDevices(implode(', ', $block_devices));
      $uid > 0 ?: $entity->setOwnerById($uid);
      $entity->save();
    }
    else {
      $entity = OpenStackInstance::create([
        'cloud_context' => $cloud_context,
        'name' => $instance_name ?? $instance['InstanceId'],
        'account_id' => $instance['reservation_ownerid'],
        'security_groups' => implode(', ', $security_groups),
        'instance_id' => $instance['InstanceId'],
        'instance_type' => $instance['InstanceType'],
        'availability_zone' => $instance['Placement']['AvailabilityZone'],
        'tenancy' => $instance['Placement']['Tenancy'] ?? NULL,
        'instance_state' => $instance['State']['Name'],
        'public_dns' => $instance['PublicDnsName'],
        'public_ip' => $instance['PublicIpAddress'] ?? NULL,
        'private_dns' => $instance['PrivateDnsName'] ?? NULL,
        'key_pair_name' => $instance['KeyName'],
        'is_monitoring' => $instance['Monitoring']['State'] ?? NULL,
        'vpc_id' => $instance['VpcId'] ?? NULL,
        'subnet_id' => $instance['SubnetId'] ?? NULL,
        'source_dest_check' => $instance['SourceDestCheck'] ?? NULL,
        'ebs_optimized' => $instance['EbsOptimized'] ?? NULL,
        'root_device_type' => $instance['RootDeviceType'] ?? NULL,
        'root_device' => $instance['RootDeviceName'] ?? NULL,
        'image_id' => $instance['ImageId'],
        'placement_group' => $instance['Placement']['GroupName'] ?? NULL,
        'virtualization' => $instance['VirtualizationType'] ?? NULL,
        'reservation' => $instance['reservation_id'],
        'ami_launch_index' => $instance['AmiLaunchIndex'],
        'host_id' => $instance['Placement']['HostId'] ?? NULL,
        'affinity' => $instance['Placement']['Affinity'] ?? NULL,
        'state_transition_reason' => $instance['StateTransitionReason'] ?? NULL,
        'instance_lock' => FALSE,
        'launch_time' => strtotime($instance['LaunchTime']->__toString()),
        'created' => strtotime($instance['LaunchTime']->__toString()),
        'changed' => $timestamp,
        'refreshed' => $timestamp,
        'uid' => $uid,
        'termination_timestamp' => $termination_timestamp,
        'termination_protection' => $termination_protection,
        'user_data' => $user_data,
        'tags' => $tags,
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
   */
  public static function updateImage($cloud_context, array $image) {
    /* @var \Drupal\openstack\Service\OpenStackEc2Service $openStackEc2Service */
    $openStackEc2Service = \Drupal::service('openstack.ec2');
    $openStackEc2Service->setCloudContext($cloud_context);
    $timestamp = time();

    $block_device_mappings = [];
    $image_block_device = $image['BlockDeviceMappings'] ?? [];
    foreach ($image_block_device ?: [] as $block_device) {
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

    $image_name = $image['Name'] ?? '';
    $name = $openStackEc2Service->getTagName($image, $image_name);
    $uid = $openStackEc2Service->getUidTagValue($image, OpenStackImage::TAG_CREATED_BY_UID);
    $entity_id = $openStackEc2Service->getEntityId('openstack_image', 'image_id', $image['ImageId']);

    // Skip if $entity already exists, by updating 'refreshed' time.
    if (!empty($entity_id)) {
      try {
        $entity = OpenStackImage::load($entity_id);
        $entity->setName($name);
        $entity->setRefreshed($timestamp);
        $entity->setVisibility($image['Public']);
        $entity->setStatus($image['State']);
        $entity->setBlockDeviceMappings($block_device_mappings);
        $uid > 0 ?: $entity->setOwnerById($uid);
        $entity->save();
      }
      catch (\Exception $e) {
        \Drupal::service('cloud')->handleException($e);
      }
    }
    else {
      $entity = OpenStackImage::create([
        'cloud_context' => $cloud_context,
        'image_id' => $image['ImageId'],
        'account_id' => $image['OwnerId'],
        'architecture' => $image['Architecture'] ?? '',
        'virtualization_type' => $image['VirtualizationType'] ?? '',
        'root_device_type' => $image['RootDeviceType'] ?? '',
        'root_device_name' => $image['RootDeviceName'] ?? '',
        'ami_name' => $image['Name'] ?? '',
        'name' => $image['Name'] ?? '',
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
   * Update or create a key pair entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $key_pair
   *   The key_pair array.
   */
  public static function updateKeyPair($cloud_context, array $key_pair) {
    /* @var \Drupal\openstack\Service\OpenStackEc2Service $openStackEc2Service */
    $openStackEc2Service = \Drupal::service('openstack.ec2');
    $openStackEc2Service->setCloudContext($cloud_context);
    $timestamp = time();

    $entity_id = $openStackEc2Service->getEntityId('openstack_key_pair', 'key_pair_name', $key_pair['KeyName']);

    if (!empty($entity_id)) {
      $entity = OpenStackKeyPair::load($entity_id);
      if (!empty($entity)) {
        $entity->setRefreshed($timestamp);
        $entity->save();
      }
    }
    else {
      $entity = OpenStackKeyPair::create([
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
   * Update or create a security group entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $security_group
   *   The security_group array.
   */
  public static function updateSecurityGroup($cloud_context, array $security_group) {
    /* @var \Drupal\openstack\Service\OpenStackEc2Service $openStackEc2Service */
    $openStackEc2Service = \Drupal::service('openstack.ec2');
    $openStackEc2Service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $openStackEc2Service->getTagName($security_group, $security_group['GroupName']);
    $uid = $openStackEc2Service->getUidTagValue($security_group, OpenStackSecurityGroup::TAG_CREATED_BY_UID);
    $entity_id = $openStackEc2Service->getEntityId('openstack_security_group', 'group_id', $security_group['GroupId']);

    // Skip if $entity already exists, by updating 'refreshed' time.
    if (!empty($entity_id)) {
      /* @var \Drupal\openstack\Entity\SecurityGroup $entity */
      try {
        $entity = OpenStackSecurityGroup::load($entity_id);
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
      $entity = OpenStackSecurityGroup::create([
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
      $vpcs = $openStackEc2Service->describeVpcs([
        'VpcIds' => [$security_group['VpcId']],
      ]);
      if ($vpcs['Vpcs']) {
        $default = $vpcs['Vpcs'][0]['IsDefault'];
        $entity->setDefaultVpc($default);
      }
    }

    // Setup the Inbound permissions.
    if (isset($security_group['IpPermissions'])) {
      $openStackEc2Service->setupIpPermissions($entity, 'ip_permission', $security_group['IpPermissions']);
    }

    // Setup outbound permissions.
    if (isset($security_group['VpcId']) && isset($security_group['IpPermissionsEgress'])) {
      $openStackEc2Service->setupIpPermissions($entity, 'outbound_permission', $security_group['IpPermissionsEgress']);
    }
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
    /* @var \Drupal\openstack\Service\OpenStackEc2Service $openStackEc2Service */
    $openStackEc2Service = \Drupal::service('openstack.ec2');
    $openStackEc2Service->setCloudContext($cloud_context);

    $timestamp = time();

    $attachments = [];
    foreach ($volume['Attachments'] ?: [] as $attachment) {
      $attachments[] = $attachment['InstanceId'];
    }

    $name = $openStackEc2Service->getTagName($volume, $volume['VolumeId']);
    $entity_id = $openStackEc2Service->getEntityId('openstack_volume', 'volume_id', $volume['VolumeId']);
    $uid = $openStackEc2Service->getUidTagValue($volume, OpenStackVolume::TAG_CREATED_BY_UID);

    if ($uid === 0) {
      // Inherit the volume uid from the instance that launched it.
      if (count($attachments)) {
        $uid = $openStackEc2Service->getInstanceUid($attachments[0]);
      }
    }

    // Skip if $entity already exists, by updating 'refreshed' time.
    if (!empty($entity_id)) {
      /* @var \Drupal\openstack\Entity\Volume $entity */
      $entity = OpenStackVolume::load($entity_id);
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

      $uid > 0 ?: $entity->setOwnerById($uid);
      $entity->save();
    }
    else {
      $entity = OpenStackVolume::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'volume_id' => $volume['VolumeId'],
        'size' => $volume['Size'],
        'state' => $volume['State'],
        'volume_status' => $volume['VirtualizationType'] ?? NULL,
        'attachment_information' => implode(', ', $attachments),
        'volume_type' => $volume['VolumeType'],
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
   * Update or create a snapshot entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $snapshot
   *   The snapshot array.
   */
  public static function updateSnapshot($cloud_context, array $snapshot) {
    /* @var \Drupal\openstack\Service\OpenStackEc2Service $openStackEc2Service */
    $openStackEc2Service = \Drupal::service('openstack.ec2');
    $openStackEc2Service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $openStackEc2Service->getTagName($snapshot, $snapshot['SnapshotId']);
    $entity_id = $openStackEc2Service->getEntityId('openstack_snapshot', 'snapshot_id', $snapshot['SnapshotId']);
    $uid = $openStackEc2Service->getUidTagValue($snapshot, OpenStackSnapshot::TAG_CREATED_BY_UID);

    if (isset($snapshot['Encrypted'])) {
      $snapshot_encrypetd = $snapshot['Encrypted'] === FALSE ? 'Not Encrypted' : 'Encrypted';
    }

    if (!empty($entity_id)) {
      $entity = OpenStackSnapshot::load($entity_id);
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
      $entity = OpenStackSnapshot::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'snapshot_id' => $snapshot['SnapshotId'],
        'size' => $snapshot['VolumeSize'],
        'description' => $snapshot['Description'],
        'status' => $snapshot['State'],
        'volume_id' => $snapshot['VolumeId'],
        'progress' => $snapshot['Progress'],
        'encrypted' => $snapshot_encrypetd ?? NULL,
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
   * Update or create a network interface entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $network_interface
   *   The network interface array.
   */
  public static function updateNetworkInterface($cloud_context, array $network_interface) {
    /* @var \Drupal\openstack\Service\OpenStackEc2Service $openStackEc2Service */
    $openStackEc2Service = \Drupal::service('openstack.ec2');
    $openStackEc2Service->setCloudContext($cloud_context);

    $timestamp = time();

    // Set up the primary and secondary private IP addresses.
    // Setup the allocation_ids.  The allocation_ids are used during Floating
    // IP assignment.
    $primary_private_ip = NULL;
    $secondary_private_ip = NULL;
    $primary_association_id = NULL;
    $secondary_association_id = NULL;
    $public_ips = NULL;

    foreach ($network_interface['PrivateIpAddresses'] ?: [] as $private_ip_address) {
      if (!empty($private_ip_address['Primary'])) {
        $primary_private_ip = $private_ip_address['PrivateIpAddress'];
        if (!empty($private_ip_address['Association'])) {
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
        if (!empty($private_ip_address['Association'])) {
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
    // If this key changes to 'Tags' on OpenStack API, this block needs to be
    // deleted.
    if (!empty($network_interface['TagSet'])) {
      $network_interface['Tags'] = $network_interface['TagSet'];
    }

    $name = $openStackEc2Service->getTagName($network_interface, $network_interface['NetworkInterfaceId']);
    $uid = $openStackEc2Service->getUidTagValue($network_interface, OpenStackNetworkInterface::TAG_CREATED_BY_UID);
    $entity_id = $openStackEc2Service->getEntityId('openstack_network_interface', 'network_interface_id', $network_interface['NetworkInterfaceId']);

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
      /* @var \Drupal\openstack\Entity\NetworkInterface $entity */
      $entity = OpenStackNetworkInterface::load($entity_id);
      $entity->setName($name);
      $entity->setRefreshed($timestamp);
      $entity->setPrimaryPrivateIp($primary_private_ip);
      $entity->setSecondaryPrivateIp($secondary_private_ip);
      $entity->setAssociationId($primary_association_id);
      $entity->setSecondaryAssociationId($secondary_association_id);
      $entity->setSecurityGroups($security_groups);
      if (!empty($public_ips)) {
        $public_ips = implode(', ', $public_ips);
      }

      $entity->setPublicIps($public_ips);
      $entity->setVpcId($network_vpc_id);
      $entity->setStatus($network_interface['Status']);
      $uid > 0 ?: $entity->setOwnerById($uid);
      $entity->save();
    }
    else {
      $entity = OpenStackNetworkInterface::create([
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
   * Update or create a Floating IP entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $floating_ip
   *   The Floating IP array.
   */
  public static function updateFloatingIp($cloud_context, array $floating_ip) {
    /* @var \Drupal\openstack\Service\OpenStackEc2ServiceInterface */
    $openStackEc2Service = \Drupal::service('openstack.ec2');
    $openStackEc2Service->setCloudContext($cloud_context);
    $timestamp = time();

    $name = $openStackEc2Service->getTagName($floating_ip, $floating_ip['PublicIp']);
    $uid = $openStackEc2Service->getUidTagValue($floating_ip, OpenStackFloatingIp::TAG_CREATED_BY_UID);
    $entity_id = $openStackEc2Service->getEntityId('openstack_floating_ip', 'public_ip', $floating_ip['PublicIp']);

    // Skip if $entity already exists, by updating 'refreshed' time.
    if (!empty($entity_id)) {
      $entity = OpenStackFloatingIp::load($entity_id);

      // Update fields.
      try {
        $entity->setName($name);
        $entity->setInstanceId($floating_ip['InstanceId'] ?? '');
        $entity->setNetworkInterfaceId($floating_ip['NetworkInterfaceId'] ?? '');
        $entity->setPrivateIpAddress($floating_ip['PrivateIpAddress'] ?? '');
        $entity->setNetworkInterfaceOwner($floating_ip['NetworkInterfaceOwnerId'] ?? '');
        $entity->setAllocationId($floating_ip['AllocationId'] ?? '');
        $entity->setAssociationId($floating_ip['AssociationId'] ?? '');
        $entity->setDomain($floating_ip['Domain'] ?? '');

        $entity->setRefreshed($timestamp);
        $uid > 0 ?: $entity->setOwnerById($uid);
        $entity->save();
      }
      catch (\Exception $e) {
        \Drupal::service('cloud')->handleException($e);
      }
    }
    else {
      $entity = OpenStackFloatingIp::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'public_ip' => $floating_ip['PublicIp'],
        'instance_id' => $floating_ip['InstanceId'] ?? '',
        'network_interface_id' => $floating_ip['NetworkInterfaceId'] ?? '',
        'private_ip_address' => $floating_ip['PrivateIpAddress'] ?? '',
        'network_interface_owner' => $floating_ip['NetworkInterfaceOwnerId'] ?? '',
        'allocation_id' => $floating_ip['AllocationId'] ?? '',
        'association_id' => $floating_ip['AssociationId'] ?? '',
        'domain' => $floating_ip['Domain'] ?? '',
        'created' => $timestamp,
        'changed' => $timestamp,
        'refreshed' => $timestamp,
        'uid' => $uid,
      ]);
      $entity->save();
    }
  }

}
