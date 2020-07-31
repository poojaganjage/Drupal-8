<?php

namespace Drupal\Tests\aws_cloud\Traits;

use Drupal\Component\Serialization\Yaml;
use Drupal\gapps\Service\GoogleSpreadsheetService;
use Drupal\Tests\cloud\Functional\Utils;
use Drupal\Tests\aws_cloud\Functional\Ec2\ImageTest;

/**
 * The trait creating mock data for aws cloud testing.
 */
trait AwsCloudTestMockTrait {

  /**
   * Initialize mock instance types.
   *
   * The mock instance types will be saved to configuration
   * aws_cloud_mock_instance_types.
   */
  protected function initMockInstanceTypes(): void {

    // The variable $mock_instance_types should be fine as a dummy table.
    // For testing, we use:
    // "c${num}.xlarge" in createServerTemplateTestFormData,
    // "t${num}.micro"  in createInstanceTestFormData and
    // "t${num}.small"  in createInstanceTestEntity.
    $mock_instance_types = [
      'c3.xlarge' => 'c3.xlarge:2:10:4:0.085:876:1629',
      'c4.xlarge' => 'c4.xlarge:4:20:8:0.17:1751:3529',
      'c5.xlarge' => 'c5.xlarge:8:39:16:0.34:3503:6518',
      't1.micro'  => 't1.micro:2:1:1:0.0104:53:103',
      't2.micro'  => 't2.micro:2:2:2:0.0208:107:206',
      't3.micro'  => 't3.micro:2:4:4:0.0416:213:412',
      't1.small'  => 't1.small:1:3:3.75:0.067:453:687',
      't2.small'  => 't2.small:2:6.5:7.5:0.133:713:1373',
      't3.small'  => 't3.small:4:13:15:0.266:1428:2746',
    ];

    $config = \Drupal::configFactory()->getEditable('aws_cloud.settings');
    $config->set('aws_cloud_mock_instance_types', json_encode($mock_instance_types))
      ->save();
  }

  /**
   * Mock the GoogleSpreadsheetService.
   *
   * The mock up will return an spreadsheet URL.
   */
  private function initMockGoogleSpreadsheetService(): void {
    $mock_spreadsheet_service = $this
      ->getMockBuilder(GoogleSpreadsheetService::class)
      ->disableOriginalConstructor()
      ->getMock();

    $spreadsheet_id = $this->random->name(44, TRUE);
    $spreadsheet_url = "https://docs.google.com/spreadsheets/d/${spreadsheet_id}/edit";
    $mock_spreadsheet_service->expects($this->any())
      ->method('createOrUpdate')
      ->willReturn($spreadsheet_url);

    // Provide a mock service container, for the services our module uses.
    $container = \Drupal::getContainer();
    $container->set('gapps.google_spreadsheet', $mock_spreadsheet_service);
  }

  /**
   * Add Elastic IP mock data.
   *
   * @param string $name
   *   The Elastic IP name.
   * @param string $public_ip
   *   The public IP.
   * @param string $domain
   *   The domain.
   *
   * @throws \Exception
   */
  protected function addElasticIpMockData($name = '', $public_ip = '', $domain = 'standard'): void {
    $mock_data = $this->getMockDataFromConfig();
    $elastic_ip = [
      'AllocationId' => $name,
      'PublicIp' => $public_ip,
      'PrivateIpAddress' => Utils::getRandomPrivateIp(),
      'Domain' => $domain,
    ];

    $mock_data['DescribeAddresses']['Addresses'][] = $elastic_ip;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Add instance mock data.
   *
   * @param string $test_class
   *   The InstanceTest class.  It can be InstanceTest::class or
   *   OpenStackInstanceTest::class.
   * @param string $name
   *   Instance name.
   * @param string $key_pair_name
   *   Key pair name.
   * @param array $regions
   *   The Regions.
   * @param string $state
   *   Instance state.
   * @param string $schedule_value
   *   Schedule value.
   *
   * @return string
   *   The instance ID.
   *
   * @throws \Exception
   */
  protected function addInstanceMockData($test_class, $name = '', $key_pair_name = '', array $regions = [], $state = 'running', $schedule_value = ''): string {
    // Prepare the mock data for an instance.
    $public_ip = Utils::getRandomPublicIp();
    $private_ip = Utils::getRandomPrivateIp();

    $region = $regions[array_rand($regions)];

    $vars = [
      'name' => $name,
      'key_name' => $key_pair_name,
      'account_id' => random_int(100000000000, 999999999999),
      'instance_id' => "i-{$this->getRandomId()}",
      'reservation_id' => "r-{$this->getRandomId()}",
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
      'vpc_id' => "vpc-{$this->getRandomId()}",
      'subnet_id' => "subnet-{$this->getRandomId()}",
      'image_id' => "ami-{$this->getRandomId()}",
      'reason' => $this->random->string(16, TRUE),
      'state' => $state,
      'uid' => $this->loggedInUser->id(),
    ];

    $vars = array_merge($this->getMockDataTemplateVars(), $vars);

    $instance_mock_data_content = $this->getMockDataFileContent($test_class, $vars, '_instance');

    $instance_mock_data = Yaml::decode($instance_mock_data_content);
    // OwnerId and ReservationId need to be set.
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeInstances']['Reservations'][0]['OwnerId'] = $this->random->name(8, TRUE);
    $mock_data['DescribeInstances']['Reservations'][0]['ReservationId'] = $this->random->name(8, TRUE);

    // Add Schedule information if available.
    if (!empty($schedule_value)) {
      $instance_mock_data['Tags'][] = [
        'Key' => 'Schedule',
        'Value' => $schedule_value,
      ];
    }

    $mock_data['DescribeInstances']['Reservations'][0]['Instances'][] = $instance_mock_data;
    $this->updateMockDataToConfig($mock_data);
    return $vars['instance_id'];
  }

  /**
   * Add Network Interface mock data.
   *
   * @param array $data
   *   Array of Network Interface data.
   */
  protected function addNetworkInterfaceMockData(array &$data): void {

    $mock_data = $this->getMockDataFromConfig();
    $vars = $this->latestTemplateVars;

    $network_interface = [
      'NetworkInterfaceId' => $vars['network_interface_id'],
      'VpcId' => $vars['vpc_id'],
      'Description' => $data['description'],
      'SubnetId' => $data['subnet_id'],
      'MacAddress' => NULL,
      'Status' => 'in-use',
      'PrivateDnsName' => NULL,
      'OwnerId' => $this->random->name(8, TRUE),
      'SourceDestCheck' => NULL,
      'PrivateIpAddress' => $data['primary_private_ip'],
      'Attachment' => [
        'AttachmentId' => $data['attachment_id'] ?? NULL,
        'InstanceOwnerId' => NULL,
        'Status' => NULL,
        'InstanceId' => NULL,
        'DeviceIndex' => '',
        'DeleteOnTermination' => NULL,
      ],
      'Association' => [
        'AllocationId' => '',
      ],
      'PrivateIpAddresses' => [[
        'Primary' => TRUE,
        'PrivateIpAddress' => $data['primary_private_ip'],
      ],
      ],
      'Groups' => [['GroupName' => $data['security_groups[]']]],
    ];

    $data['name'] = $network_interface['NetworkInterfaceId'];
    $mock_data['DescribeNetworkInterfaces']['NetworkInterfaces'][] = $network_interface;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Delete first Elastic IP in mock data.
   */
  protected function deleteFirstElasticIpMockData(): void {
    $mock_data = $this->getMockDataFromConfig();
    $addresses = $mock_data['DescribeAddresses']['Addresses'];
    array_shift($addresses);
    $mock_data['DescribeAddresses']['Addresses'] = $addresses;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Delete all Elastic IP in mock data.
   */
  protected function deleteAllElasticIpInMockData(): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeAddresses']['Addresses'] = [];
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update domain in mock data.
   *
   * @param string $domain
   *   A domain.
   */
  protected function updateDomainMockData($domain): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['AllocateAddress']['Domain'] = $domain;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update Elastic IP mock data.
   *
   * @param int $elastic_ip_index
   *   The index of Elastic IP.
   * @param string $name
   *   The Elastic IP name.
   * @param string $association_id
   *   The association ID.
   * @param string $instance_id
   *   The instance ID.
   */
  protected function updateElasticIpMockData($elastic_ip_index = 0, $name = '', $association_id = '', $instance_id = ''): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeAddresses']['Addresses'][$elastic_ip_index]['AllocationId'] = $name;
    $mock_data['DescribeAddresses']['Addresses'][$elastic_ip_index]['AssociationId'] = $association_id;
    $mock_data['DescribeAddresses']['Addresses'][$elastic_ip_index]['InstanceId'] = $instance_id;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update instance in mock data.
   *
   * @param string $test_class
   *   The InstanceTest class.  It can be InstanceTest::class or
   *   OpenStackInstanceTest::class.
   * @param int $index
   *   Instance index.
   * @param string $name
   *   Instance name.
   * @param array $regions
   *   The Regions.
   * @param string $state
   *   Instance state.
   *
   * @throws \Exception
   */
  protected function updateInstanceMockData($test_class, $index = 0, $name = '', array $regions = [], $state = 'stopped'): void {
    $mock_data = $this->getMockDataFromConfig();

    if (empty($name)) {
      // Prepare the mock data for an instance.
      $public_ip = Utils::getRandomPublicIp();
      $private_ip = Utils::getRandomPrivateIp();

      $region = $regions[array_rand($regions)];

      // These key-value pairs are replaced into the place holder such as {{..}}
      // in <ResourceName>Test.hml.
      $vars = [
        // 12 digits.
        'account_id' => random_int(100000000000, 999999999999),
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
        'uid' => $this->loggedInUser->id(),
      ];

      // Create a mock data for an Instance.
      $instance_mock_data_content = $this->getMockDataFileContent($test_class, $vars, '_instance');
      $instance_mock_data = Yaml::decode($instance_mock_data_content);

      // OwnerId and ReservationId need to be set.
      $mock_data['DescribeInstances']['Reservations'][0]['OwnerId'] = $this->random->name(8, TRUE);
      $mock_data['DescribeInstances']['Reservations'][0]['ReservationId'] = $this->random->name(8, TRUE);
      $mock_data['DescribeInstances']['Reservations'][0]['Instances'][$index] = $instance_mock_data;
    }

    $mock_data['DescribeInstances']['Reservations'][0]['Instances'][$index]['Tags'][0]['Value'] = $name;
    $mock_data['DescribeInstances']['Reservations'][0]['Instances'][$index]['State']['Name'] = $state;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Add Image mock data.
   *
   * @param string $name
   *   The Image name.
   * @param string $description
   *   The description.
   * @param string $cloud_context
   *   The cloud context.
   *
   * @return array
   *   The image items of DescribeImages mock data.
   */
  protected function addImageMockData($name, $description, $cloud_context): array {
    $cloud_config_plugin = \Drupal::service('plugin.manager.cloud_config_plugin');
    $cloud_config_plugin->setCloudContext($cloud_context);
    $cloud_config = $cloud_config_plugin->loadConfigEntity();
    $account_id = $cloud_config->get('field_account_id')->value;

    $mock_data = $this->getMockDataFromConfig();
    $vars = $this->getMockDataTemplateVars();

    $mock_data['CreateImage'] = [
      'ImageId' => $vars['image_id'],
    ];
    $this->updateMockDataToConfig($mock_data);

    $image = [
      'ImageId' => $vars['image_id'],
      'OwnerId' => $account_id,
      'Architecture' => $vars['architecture'],
      'Description' => $description,
      'VirtualizationType' => 'hvm',
      'RootDeviceType' => 'ebs',
      'RootDeviceName' => '/dev/sda1',
      'Name' => $name,
      'KernelId' => $vars['kernel_id'],
      'RamdiskId' => $vars['ramdisk_id'],
      'ImageType' => $vars['image_type'],
      'ProductCodes' => ['ProductCode' => [$vars['product_code1'], $vars['product_code2']]],
      'ImageLocation' => $vars['image_location'],
      'StateReason' => ['Message' => $vars['state_reason_message']],
      'Platform' => $vars['platform'],
      'Public' => $vars['public'],
      'State' => $vars['state'],
      'CreationDate' => $vars['creation_date'],
      'BlockDeviceMappings' => [['DeviceName' => NULL]],
      'Hypervisor' => $vars['hypervisor'],
    ];

    $mock_data['DescribeImages']['Images'][] = $image;
    $this->updateMockDataToConfig($mock_data);
    return $image;
  }

  /**
   * Update image mock data.
   *
   * @param array $images
   *   The image data.
   */
  protected function updateImagesMockData(array $images): void {
    $mock_data = $this->getMockDataFromConfig();
    // Clear all existing images.
    $mock_data['DescribeImages']['Images'] = [];
    foreach ($images ?: [] as $image) {
      $items = [];
      foreach ($image ?: [] as $key => $value) {
        $items[$key] = $value;
      }
      $mock_data['DescribeImages']['Images'][] = $items;
    }
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Delete first Image in mock data.
   */
  protected function deleteFirstImageMockData(): void {
    $mock_data = $this->getMockDataFromConfig();
    $images = $mock_data['DescribeImages']['Images'];
    array_shift($images);
    $mock_data['DescribeImages']['Images'] = $images;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Add KeyPair mock data.
   *
   * @param string $name
   *   The key pair name.
   */
  protected function addKeyPairMockData($name): void {
    $mock_data = $this->getMockDataFromConfig();
    $vars = $this->getMockDataTemplateVars();
    $key_pair = [
      'KeyName' => $name,
      'KeyFingerprint' => $vars['key_fingerprint'],
    ];
    $mock_data['DescribeKeyPairs']['KeyPairs'][] = $key_pair;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Delete first Key Pair in mock data.
   */
  protected function deleteFirstKeyPairMockData(): void {
    $mock_data = $this->getMockDataFromConfig();
    $key_pairs = $mock_data['DescribeKeyPairs']['KeyPairs'];
    array_shift($key_pairs);
    $mock_data['DescribeKeyPairs']['KeyPairs'] = $key_pairs;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update Key Pair mock data.
   *
   * @param int $key_pair_index
   *   The index of key pair.
   * @param string $name
   *   The key pair name.
   */
  protected function updateKeyPairMockData($key_pair_index, $name): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeKeyPairs']['KeyPairs'][$key_pair_index]['KeyName'] = $name;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Delete first Network Interface in mock data.
   */
  protected function deleteFirstNetworkInterfaceMockData(): void {
    $mock_data = $this->getMockDataFromConfig();
    $network_interfaces = $mock_data['DescribeNetworkInterfaces']['NetworkInterfaces'];
    array_shift($network_interfaces);
    $mock_data['DescribeNetworkInterfaces']['NetworkInterfaces'] = $network_interfaces;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update Network Interface mock data.
   *
   * @param int $network_interface_index
   *   The index of Network Interface.
   * @param string $name
   *   The network Interface name.
   */
  protected function updateNetworkInterfaceMockData($network_interface_index, $name): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeNetworkInterfaces']['NetworkInterfaces'][$network_interface_index]['NetworkInterfaceId'] = $name;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Add security group mock data.
   *
   * @param string $name
   *   The security group name.
   * @param string $description
   *   The description.
   * @param string $vpc_id
   *   The VPC ID.
   *
   * @return string
   *   The security group ID.
   */
  protected function addSecurityGroupMockData($name, $description, $vpc_id = NULL): string {
    $mock_data = $this->getMockDataFromConfig();
    $vars = $this->getMockDataTemplateVars();
    $security_group = [
      'GroupId' => $vars['group_id'],
      'GroupName' => $name,
      'Description' => $description,
      'VpcId' => $vpc_id ?? $vars['vpc_id'],
      'OwnerId' => $this->random->name(8, TRUE),
    ];
    $mock_data['DescribeSecurityGroups']['SecurityGroups'][] = $security_group;
    $this->updateMockDataToConfig($mock_data);
    return $vars['group_id'];
  }

  /**
   * Delete first security group in mock data.
   */
  protected function deleteFirstSecurityGroupMockData(): void {
    $mock_data = $this->getMockDataFromConfig();
    $security_groups = $mock_data['DescribeSecurityGroups']['SecurityGroups'];
    array_shift($security_groups);
    $mock_data['DescribeSecurityGroups']['SecurityGroups'] = $security_groups;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Add Snapshot mock data.
   *
   * @param string $name
   *   The snapshot name.
   * @param string $volume_id
   *   The volume ID.
   * @param string $description
   *   The description.
   *
   * @return string
   *   An array of possible key and value options.
   */
  protected function addSnapshotMockData(&$name, $volume_id, $description): string {
    $mock_data = $this->getMockDataFromConfig();
    $vars = $this->getMockDataTemplateVars();

    $snapshot = [
      'SnapshotId' => $vars['snapshot_id'],
      'VolumeSize' => 10,
      'Description' => $description,
      'State' => 'completed',
      'VolumeId' => $volume_id,
      'Progress' => 100,
      'Encrypted' => FALSE,
      'KmsKeyId' => NULL,
      'OwnerId' => $this->random->name(8, TRUE),
      'OwnerAlias' => NULL,
      'StateMessage' => NULL,
      'StartTime' => $vars['start_time'],
    ];

    $name = $snapshot['SnapshotId'];

    $mock_data['DescribeSnapshots']['Snapshots'][] = $snapshot;
    $this->updateMockDataToConfig($mock_data);

    return $vars['snapshot_id'];
  }

  /**
   * Delete first Snapshot in mock data.
   */
  protected function deleteFirstSnapshotMockData(): void {
    $mock_data = $this->getMockDataFromConfig();
    $snapshots = $mock_data['DescribeSnapshots']['Snapshots'];
    array_shift($snapshots);
    $mock_data['DescribeSnapshots']['Snapshots'] = $snapshots;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update Snapshot mock data.
   *
   * @param int $snapshot_index
   *   The index of Snapshot.
   * @param string $name
   *   The snapshot name.
   */
  protected function updateSnapshotMockData($snapshot_index, $name): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeSnapshots']['Snapshots'][$snapshot_index]['Tags'][0] = ['Key' => 'Name', 'Value' => $name];
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Add Volume mock data.
   *
   * @param array $data
   *   Array of volume data.
   * @param string $tag_created_uid
   *   The tag created by uid in mock data.
   *
   * @throws \Exception
   */
  protected function addVolumeMockData(array $data, $tag_created_uid): void {
    $mock_data = $this->getMockDataFromConfig();
    $vars = $this->getMockDataTemplateVars();

    $volume = [
      'VolumeId' => $data['name'],
      'Attachments' => [
        ['InstanceId' => NULL],
      ],
      'State' => 'available',
      'SnapshotId' => $data['snapshot_id'],
      'Size' => $data['size'],
      'VirtualizationType' => NULL,
      'VolumeType' => $data['volume_type'],
      'Iops' => $data['iops'] ?? '',
      'AvailabilityZone' => $data['availability_zone'],
      'Encrypted' => $data['encrypted'] ?? '',
      'KmsKeyId' => NULL,
      'CreateTime' => $vars['create_time'],
      'Tags' => [
        [
          'Key' => $tag_created_uid,
          'Value' => Utils::getRandomUid(),
        ],
      ],
    ];

    $mock_data['DescribeVolumes']['Volumes'][] = $volume;
    $this->updateMockDataToConfig($mock_data);

    $snapshot_name = 'snapshot-name' . $this->random->name(10, TRUE);
    $this->updateDescribeSnapshotsMockData([['id' => $data['snapshot_id'], 'name' => $snapshot_name]]);
  }

  /**
   * Delete first Volume in mock data.
   */
  protected function deleteFirstVolumeMockData(): void {
    $mock_data = $this->getMockDataFromConfig();
    $addresses = $mock_data['DescribeVolumes']['Volumes'];
    array_shift($addresses);
    $mock_data['DescribeVolumes']['Volumes'] = $addresses;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update Volume mock data.
   *
   * @param int $volume_index
   *   The index of Volume.
   * @param string $name
   *   The volume name.
   * @param string $instance_id
   *   The instance ID.
   */
  protected function updateVolumeMockData($volume_index, $name, $instance_id): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeVolumes']['Volumes'][$volume_index]['VolumeId'] = $name;
    $mock_data['DescribeVolumes']['Volumes'][$volume_index]['InstanceId'] = $instance_id;
    $mock_data['DescribeVolumes']['Volumes'][$volume_index]['State'] = 'in-use';
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update the volume state and instance.
   *
   * @param string $api
   *   The API data.
   * @param string $device
   *   The device.
   * @param string $volume_id
   *   The volume_id.
   * @param string $instance_id
   *   The instance_id.
   * @param string $state
   *   The state.
   */
  protected function updateAttachDetachVolumeMockData($api, $device, $volume_id, $instance_id, $state): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data[$api] = [
      'Device' => $device,
      'InstanceId' => $instance_id,
      'State' => $state,
      'VolumeId' => $volume_id,
    ];
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Delete first instance in mock data.
   */
  protected function deleteFirstInstanceMockData(): void {
    $mock_data = $this->getMockDataFromConfig();
    $instances = $mock_data['DescribeInstances']['Reservations'][0]['Instances'];
    array_shift($instances);
    $mock_data['DescribeInstances']['Reservations'][0]['Instances'] = $instances;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update security group in mock data.
   *
   * @param string $security_group_name1
   *   Security group name1.
   * @param string $security_group_name2
   *   Security group name2.
   */
  protected function updateSecurityGroupsMockData($security_group_name1, $security_group_name2): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeInstances']['Reservations'][0]['Instances'][0]['SecurityGroups']
      = [['GroupName' => $security_group_name1], ['GroupName' => $security_group_name2]];
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Add VPC to mock data.
   *
   * @param array $vpc_data
   *   VPC data.
   * @param string $vpc_id
   *   VPC ID.
   */
  protected function addVpcMockData(array $vpc_data, $vpc_id): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeVpcs']['Vpcs'][] = [
      'VpcId' => $vpc_id,
      'State' => 'available',
      'CidrBlock' => $vpc_data['cidr_block'],
      'CidrBlockAssociationSet' => [
        [
          'CidrBlock' => $vpc_data['cidr_block'],
          'AssociationId' => 'vpc-cidr-assoc' . $this->getRandomId(),
          'CidrBlockState' => [
            'State' => 'associated',
          ],
        ],
      ],
      'Ipv6CidrBlockAssociationSet' => [],
      'DhcpOptionsId' => 'dopt-' . $this->getRandomId(),
      'InstanceTenancy' => 'default',
      'IsDefault' => FALSE,
      'Tags' => [
        [
          'Key' => 'Name',
          'Value' => $vpc_data['name'],
        ],
      ],
    ];

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Add VPC Peering Connection to mock data.
   *
   * @param array $vpc_peering_connection_data
   *   VPC Peering Connection data.
   * @param string $vpc_peering_connection_id
   *   VPC Peering Connection ID.
   *
   * @throws \Exception
   */
  protected function addVpcPeeringConnectionMockData(array $vpc_peering_connection_data, $vpc_peering_connection_id): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeVpcPeeringConnections']['VpcPeeringConnections'][] = [
      'VpcPeeringConnectionId' => $vpc_peering_connection_id,
      'RequesterVpcInfo' => [
        'OwnerId' => (string) random_int(100000000000, 999999999999),
        'VpcId' => 'vpc-' . $this->getRandomId(),
        'CidrBlock' => Utils::getRandomCidr(),
        'Region' => 'ap-northeast-1',
      ],
      'AccepterVpcInfo' => [
        'OwnerId' => (string) random_int(100000000000, 999999999999),
        'VpcId' => 'vpc-' . $this->getRandomId(),
        'CidrBlock' => Utils::getRandomCidr(),
        'Region' => 'ap-northeast-1',
      ],
      'Status' => [
        'Code' => 'pending-acceptance',
        'Message' => 'Pending Acceptance',
      ],
      'Tags' => [
        [
          'Key' => 'Name',
          'Value' => $vpc_peering_connection_data['name'],
        ],
      ],
    ];

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Add subnet to mock data.
   *
   * @param array $subnet_data
   *   Subnet data.
   * @param string $subnet_id
   *   Subnet ID.
   */
  protected function addSubnetMockData(array $subnet_data, $subnet_id): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeSubnets']['Subnets'][] = [
      'VpcId' => $subnet_data['vpc_id'],
      'SubnetId' => $subnet_id,
      'State' => 'available',
      'CidrBlock' => $subnet_data['cidr_block'],
      'Tags' => [
        [
          'Key' => 'Name',
          'Value' => $subnet_data['name'],
        ],
      ],
    ];

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Delete the VPC of mock data.
   *
   * @param int $index
   *   The index of vpc.
   */
  protected function deleteVpcMockData($index): void {
    $mock_data = $this->getMockDataFromConfig();
    unset($mock_data['DescribeVpcs']['Vpcs'][$index]);
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Delete the VPC Peering Connection of mock data.
   *
   * @param int $index
   *   The index of vpc.
   */
  protected function deleteVpcPeeringConnectionMockData($index): void {
    $mock_data = $this->getMockDataFromConfig();
    unset($mock_data['DescribeVpcPeeringConnections']['VpcPeeringConnections'][$index]);
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Delete the subnet of mock data.
   *
   * @param int $index
   *   The index of subnet.
   */
  protected function deleteSubnetMockData($index): void {
    $mock_data = $this->getMockDataFromConfig();
    unset($mock_data['DescribeSubnets']['Subnets'][$index]);
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Modify the VPC of mock data.
   *
   * @param int $index
   *   The index of vpc.
   * @param array $vpc_data
   *   VPC data.
   */
  protected function modifyVpcMockData($index, array $vpc_data) {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeVpcs']['Vpcs'][$index]['Tags'] = [
      [
        'Key' => 'Name',
        'Value' => $vpc_data['name'],
      ],
    ];

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Modify the VPC of mock data.
   *
   * @param int $index
   *   The index of vpc peering connection.
   * @param array $vpc_peering_connection_data
   *   VPC Peering Connection data.
   */
  protected function modifyVpcPeeringConnectionMockData($index, array $vpc_peering_connection_data) {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeVpcPeeringConnections']['VpcPeeringConnections'][$index]['Tags'] = [
      [
        'Key' => 'Name',
        'Value' => $vpc_peering_connection_data['name'],
      ],
    ];

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Modify the subnet of mock data.
   *
   * @param int $index
   *   The index of subnet.
   * @param array $subnet_data
   *   Subnet data.
   */
  protected function modifySubnetMockData($index, array $subnet_data): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeSubnets']['Subnets'][$index]['Tags'] = [
      [
        'Key' => 'Name',
        'Value' => $subnet_data['name'],
      ],
    ];

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update describe snapshot in mock data.
   *
   * @param array $test_cases
   *   Test cases array.
   *
   * @throws \Exception
   */
  protected function updateDescribeSnapshotsMockData(array $test_cases): void {
    $random = $this->random;

    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeSnapshots'] = ['Snapshots' => []];
    foreach ($test_cases ?: [] as $test_case) {
      $snapshot = [
        'SnapshotId' => $test_case['id'],
        'VolumeSize' => 10,
        'Description' => $random->string(32, TRUE),
        'State' => 'completed',
        'VolumeId' => 'vol-' . $this->getRandomId(),
        'Progress' => '100%',
        'Encrypted' => TRUE,
        'KmsKeyId' => 'arn:aws:kms:us-east-1:123456789012:key/6876fb1b-example',
        'OwnerId' => (string) random_int(100000000000, 999999999999),
        'OwnerAlias' => 'amazon',
        'StateMessage' => $random->string(32, TRUE),
        'StartTime' => date('c'),
      ];

      if (isset($test_case['name'])) {
        $snapshot['Tags'] = [
          ['Key' => 'Name', 'Value' => $test_case['name']],
        ];
      }

      $mock_data['DescribeSnapshots']['Snapshots'][] = $snapshot;
    }

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update IAM roles to mock data.
   *
   * @param array $iam_roles
   *   The IAM roles.
   */
  protected function updateIamRolesMockData(array $iam_roles): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['ListInstanceProfiles']['InstanceProfiles'] = $iam_roles;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update describe security groups in mock data.
   *
   * @param array $security_groups
   *   Security groups array.
   */
  protected function updateDescribeSecurityGroupsMockData(array $security_groups): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeSecurityGroups']['SecurityGroups'] = $security_groups;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update instance type in mock data.
   *
   * @param string $instance_type
   *   Instance type.
   */
  protected function updateInstanceTypeMockData($instance_type): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeInstances']['Reservations'][0]['Instances'][0]['InstanceType']
      = $instance_type;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update image creation in mock data.
   *
   * @param string $image_id
   *   Image ID.
   * @param string $image_name
   *   Image name.
   * @param string $image_state
   *   Image state.
   */
  protected function updateImageCreationMockData($image_id, $image_name, $image_state): void {
    $mock_data = $this->getMockDataFromConfig();
    $vars = $this->getMockDataTemplateVars();
    $vars['image_id'] = $image_id;
    $vars['name'] = $image_name;
    $vars['state'] = $image_state;

    // Unset DescribeImages so that the state can be updated.
    unset(
      $mock_data['DescribeImages'],
      $mock_data['CreateImage']
    );

    $image_mock_data_content = $this->getMockDataFileContent(ImageTest::class, $vars);
    $image_mock_data = Yaml::decode($image_mock_data_content);

    $this->updateMockDataToConfig(array_merge($image_mock_data, $mock_data));
  }

  /**
   * Update schedule tag in mock data.
   *
   * @param string $schedule_value
   *   Schedule value.
   */
  protected function updateScheduleTagMockData($schedule_value): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeInstances']['Reservations'][0]['Instances'][0]['Tags'][0]['Name'] = 'Schedule';
    $mock_data['DescribeInstances']['Reservations'][0]['Instances'][0]['Tags'][0]['Value'] = $schedule_value;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update create volume in mock data.
   *
   * @param string $state
   *   Volume state.
   * @param string $volume_id
   *   Volume ID.
   */
  protected function updateCreateVolumeMockData($state, $volume_id): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['CreateVolume']['State'] = $state;
    $mock_data['CreateVolume']['VolumeId'] = $volume_id;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update mock data related to security group rules.
   *
   * @param array $rules
   *   The security group rules.
   * @param int $rule_type
   *   The security group rule type.
   */
  protected function updateRulesMockData(array $rules, $rule_type): void {
    $mock_data = $this->getMockDataFromConfig();

    $security_group =& $mock_data['DescribeSecurityGroups']['SecurityGroups'][0];
    $security_group['IpPermissions'] = [];
    $security_group['IpPermissionsEgress'] = [];
    foreach ($rules ?: [] as $rule) {
      $permission_name = 'IpPermissions';
      if ($rule['type'] === $rule_type) {
        $permission_name = 'IpPermissionsEgress';
      }

      $permission = [
        'IpProtocol' => 'tcp',
        'FromPort' => $rule['from_port'],
        'ToPort' => $rule['to_port'],
      ];

      if ($rule['source'] === 'ip4') {
        $permission['IpRanges'] = [
          ['CidrIp' => $rule['cidr_ip']],
        ];
      }
      elseif ($rule['source'] === 'ip6') {
        $permission['Ipv6Ranges'] = [
          ['CidrIpv6' => $rule['cidr_ip_v6']],
        ];
      }
      elseif ($rule['source'] === 'group') {
        $permission['UserIdGroupPairs'] = [
          [
            'UserId' => $rule['user_id'],
            'GroupId' => $rule['group_id'],
            'VpcId' => $rule['vpc_id'],
            'VpcPeeringConnectionId' => $rule['peering_connection_id'],
            'PeeringStatus' => $rule['peering_status'],
          ],
        ];
      }

      $security_group[$permission_name][] = $permission;
    }

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Add describe snapshots in mock data.
   *
   * @param string $snapshot_id
   *   Snapshot ID.
   */
  protected function addDescribeSnapshotsMockData($snapshot_id): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeSnapshots'] = [
      'Snapshots' => [
        [
          'SnapshotId' => $snapshot_id,
        ],
      ],
    ];
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update describe volumes in mock data.
   *
   * @param array $volumes
   *   Volumes array.
   */
  protected function updateDescribeVolumesMockData(array $volumes): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeVolumes']['Volumes'] = $volumes;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update VPCs and subnets to mock data.
   *
   * @param array $vpcs
   *   The VPCs array.
   * @param array $subnets
   *   The subnets array.
   */
  protected function updateVpcsAndSubnetsMockData(array $vpcs, array $subnets): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeVpcs']['Vpcs'] = $vpcs;
    $mock_data['DescribeSubnets']['Subnets'] = $subnets;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update DescribeVpcs in mock data.
   *
   * @param int $count
   *   The count of VPCs.
   *
   * @return array
   *   The IDs of VPCs.
   *
   * @throws \Exception
   */
  protected function updateVpcsMockData($count): array {
    $mock_data = $this->getMockDataFromConfig();
    $vpcs = [];
    $vpc_ids = [];
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $vpc_id = 'vpc-' . $this->getRandomId();
      $vpcs[] = [
        'VpcId' => $vpc_id,
        'Name' => sprintf('vpc-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'CidrBlock' => Utils::getRandomCidr(),
      ];

      $vpc_ids[] = $vpc_id;
    }

    $mock_data['DescribeVpcs']['Vpcs'] = $vpcs;
    $this->updateMockDataToConfig($mock_data);

    return $vpc_ids;
  }

  /**
   * Update tags in mock data.
   *
   * @param int $index
   *   The index to update tags.
   * @param string $entity_name
   *   The entity name in mock data.
   * @param string $key
   *   The tag key.
   * @param string $value
   *   The tag value.
   * @param bool $delete_flg
   *   Whether delete or not.
   * @param string $tag_name
   *   The tag name.
   */
  protected function updateTagsInMockData($index, $entity_name, $key, $value, $delete_flg = FALSE, $tag_name = 'Tags'): void {
    $mock_data = $this->getMockDataFromConfig();
    $describe_name = 'Describe' . $entity_name;
    $data = &$mock_data[$describe_name][$entity_name][$index];

    if ($delete_flg) {
      if (isset($data[$tag_name])) {
        foreach ($data[$tag_name] ?: [] as $idx => $tag) {
          if ($tag['Key'] === $key) {
            unset($data[$tag_name][$idx]);
          }
        }
      }
    }
    else {
      if (!isset($data[$tag_name])) {
        $data[$tag_name] = [];
      }
      else {
        foreach ($data[$tag_name] ?: [] as $idx => $tag) {
          if ($tag['Key'] === $key) {
            unset($data[$tag_name][$idx]);
          }
        }
      }
      $data[$tag_name][] = ['Key' => $key, 'Value' => $value];
    }

    $this->updateMockDataToConfig($mock_data);

  }

  /**
   * Update tags of the interface in mock data.
   *
   * @param int $index
   *   The interface index.
   * @param string $key
   *   The tag key.
   * @param string $value
   *   The tag value.
   * @param bool $delete_flg
   *   Whether delete or not.
   */
  protected function updateInstanceTagsInMockData($index, $key, $value, $delete_flg = FALSE): void {
    $mock_data = $this->getMockDataFromConfig();
    $data = &$mock_data['DescribeInstances']['Reservations'][0]['Instances'][$index];

    if ($delete_flg) {
      if (isset($data['Tags'])) {
        foreach ($data['Tags'] ?: [] as $idx => $tag) {
          if ($tag['Key'] === $key) {
            unset($data['Tags'][$idx]);
          }
        }
      }
    }
    else {
      if (!isset($data['Tags'])) {
        $data['Tags'] = [];
      }
      else {
        foreach ($data['Tags'] ?: [] as $idx => $tag) {
          if ($tag['Key'] === $key) {
            unset($data['Tags'][$idx]);
          }
        }
      }
      $data['Tags'][] = ['Key' => $key, 'Value' => $value];
    }

    $this->updateMockDataToConfig($mock_data);

  }

  /**
   * Update VPCs and subnets to mock data.
   *
   * @param array $subnets
   *   The subnets array.
   */
  protected function updateSubnetsToMockData(array $subnets): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['DescribeSubnets']['Subnets'] = $subnets;
    $this->updateMockDataToConfig($mock_data);
  }

}
