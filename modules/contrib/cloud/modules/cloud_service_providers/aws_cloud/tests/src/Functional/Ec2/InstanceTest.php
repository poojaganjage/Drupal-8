<?php

namespace Drupal\Tests\aws_cloud\Functional\Ec2;

use Drupal\aws_cloud\Entity\Ec2\Image;
use Drupal\aws_cloud\Entity\Ec2\NetworkInterface;
use Drupal\Tests\aws_cloud\Functional\AwsCloudTestBase;
use Drupal\Tests\cloud\Functional\Utils;

/**
 * Tests AWS Cloud Instance for basic operations.
 *
 * @group AWS Cloud
 */
class InstanceTest extends AwsCloudTestBase {

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

    $architecture = ['x86_64', 'arm64'];
    $image_type = ['machine', 'kernel', 'ramdisk'];
    $state = ['available', 'pending', 'failed'];
    $hypervisor = ['ovm', 'xen'];
    $public = [0, 1];

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
      'reason' => $this->random->string(16, TRUE),
      'instance_id' => 'i-' . $this->getRandomId(),
      'state' => 'running',
      'image_id' => 'ami-' . $this->getRandomId(),
      'name' => "InstanceTest::getMockDataTemplateVars - {$this->random->name(8, TRUE)}",
      'kernel_id' => 'aki-' . $this->getRandomId(),
      'ramdisk_id' => 'ari-' . $this->getRandomId(),
      'product_code1' => $this->random->name(8, TRUE),
      'product_code2' => $this->random->name(8, TRUE),
      'image_location' => $this->random->name(16, TRUE),
      'state_reason_message' => $this->random->name(8, TRUE),
      'platform' => $this->random->name(8, TRUE),
      'description' => $this->random->string(8, TRUE),
      'creation_date' => date('c'),
      'architecture' => $architecture[array_rand($architecture)],
      'image_type' => $image_type[array_rand($image_type)],
      'hypervisor' => $hypervisor[array_rand($hypervisor)],
      'public' => $public[array_rand($public)],
    ];
  }

  /**
   * Tests EC2 instance.
   *
   * @throws \Exception
   */
  public function testInstance(): void {
    $cloud_context = $this->cloudContext;
    $regions = ['us-west-1', 'us-west-2'];

    // IAM Roles.
    $iam_roles = $this->createIamRolesRandomTestFormData();
    $this->updateIamRolesMockData($iam_roles);

    // Add an empty value to IAM roles.
    $iam_roles = array_merge([[]], $iam_roles);

    // List Instance for Amazon EC2.
    $this->drupalGet("/clouds/aws_cloud/${cloud_context}/instance");
    $this->assertNoErrorMessage();

    // Launch a new Instance.
    $add = $this->createInstanceTestFormData(self::AWS_CLOUD_INSTANCE_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++, $num++) {

      // Create image.
      $image = $this->createImageTestEntity(Image::class, $i, $add[$i]['image_id'], $cloud_context);

      // Make sure if the image entity is created or not.
      $this->drupalGet("/clouds/aws_cloud/${cloud_context}/image");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($image->getName());
      $this->assertSession()->pageTextContains($image->getImageId());
      $this->assertSession()->pageTextContains($add[$i]['image_id']);

      // Setup cloud server template and instance.
      $server_template = $this->createServerTemplateTestEntity($iam_roles, $image, $cloud_context);

      // Make sure if the cloud_server_template entity is created or not.
      $this->drupalGet("/clouds/design/server_template/${cloud_context}");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($server_template->name->value);

      $this->addInstanceMockData(InstanceTest::class, $add[$i]['name'], $add[$i]['key_pair_name'], $regions);
      $this->drupalPostForm("/clouds/design/server_template/${cloud_context}/{$server_template->id()}/launch",
        [],
        $this->t('Launch'));

      $this->assertNoErrorMessage();

      // Make sure listing.
      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all instance listing exists.
      $this->drupalGet('/clouds/aws_cloud/instance');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit an Instance information.
    $edit = $this->createInstanceTestFormData(self::AWS_CLOUD_INSTANCE_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++, $num++) {

      unset(
        $edit[$i]['image_id'],
        $edit[$i]['min_count'],
        $edit[$i]['max_count'],
        $edit[$i]['key_pair_name'],
        $edit[$i]['is_monitoring'],
        $edit[$i]['availability_zone'],
        $edit[$i]['instance_type'],
        $edit[$i]['kernel_id'],
        $edit[$i]['ramdisk_id'],
        $edit[$i]['user_data']
      );

      // Change security groups.
      $security_groups = $this->createSecurityGroupRandomTestFormData();
      $this->updateDescribeSecurityGroupsMockData($security_groups);
      $edit[$i]['security_groups[]'] = [array_column($security_groups, 'GroupName')[0]];

      // Termination.
      $edit[$i]['termination_timestamp[0][value][date]'] = date('Y-m-d', time() + 365.25 * 3);
      $edit[$i]['termination_timestamp[0][value][time]'] = '00:00:00';
      $edit[$i]['termination_protection'] = '1';

      // IAM role.
      $iam_role_index = array_rand($iam_roles);
      if ($iam_role_index === 0) {
        $iam_role_name = '';
        $edit[$i]['iam_role'] = '';
      }
      else {
        $iam_role = $iam_roles[$iam_role_index]['Arn'];
        $iam_role_name = $iam_roles[$iam_role_index]['InstanceProfileName'];
        $edit[$i]['iam_role'] = $iam_role;
      }

      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/instance/$num/edit",
                            $edit[$i],
                            $this->t('Save'));

      // Termination validation.
      $this->assertSession()->pageTextContains(
        $this->t('"@name1" should be left blank if "@name2" is selected. Please leave "@name1" blank or unselect "@name2".', [
          '@name1' => $this->t('Termination Date'),
          '@name2' => $this->t('Termination Protection'),
        ])
      );

      unset(
        $edit[$i]['termination_timestamp[0][value][date]'],
        $edit[$i]['termination_timestamp[0][value][time]'],
        $edit[$i]['termination_protection']
      );

      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/instance/$num/edit",
                            $edit[$i],
                            $this->t('Save'));

      $this->updateInstanceMockData(InstanceTest::class, $i, $edit[$i]['name'], $regions);

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Instance', '%label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      if ($iam_role_name !== '') {
        $this->assertSession()->pageTextContains($iam_role_name);
      }

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance");
      $this->assertNoErrorMessage();
      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($edit[$i]['name']);
      }
    }

    // Terminate Instance.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++, $num++) {

      $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance/$num/terminate");
      $this->assertNoErrorMessage();

      $this->deleteFirstInstanceMockData();

      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/instance/$num/terminate",
                            [],
                            $this->t('Delete | Terminate'));

      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($edit[$i]['name']);
      $t_args = ['@type' => 'Instance', '@label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance");
      $this->assertNoErrorMessage();
      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextNotContains($edit[$i]['name']);
      }
    }
  }

  /**
   * Tests updating instances.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Exception
   */
  public function testUpdateInstances(): void {
    $cloud_context = $this->cloudContext;
    $regions = ['us-west-1', 'us-west-2'];

    // IAM Roles.
    $iam_roles = $this->createIamRolesRandomTestFormData();
    $this->updateIamRolesMockData($iam_roles);

    // Add an empty value to IAM roles.
    $iam_roles = array_merge([[]], $iam_roles);

    // Create image.
    $add = $this->createInstanceTestFormData();
    $image = $this->createImageTestEntity(Image::class, 0, $add[0]['image_id'], $cloud_context);

    // Make sure if the image entity is created or not.
    $this->drupalGet("/clouds/aws_cloud/${cloud_context}/image");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($image->getName());
    $this->assertSession()->pageTextContains($image->getImageId());
    $this->assertSession()->pageTextContains($add[0]['image_id']);

    // Setup cloud server template and instance.
    $server_template = $this->createServerTemplateTestEntity($iam_roles, $image, $cloud_context);

    // Make sure if the cloud_server_template entity is created or not.
    $this->drupalGet("/clouds/design/server_template/${cloud_context}");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($server_template->name->value);

    // Launch a new Instance.
    $this->addInstanceMockData(InstanceTest::class, $add[0]['name'], $add[0]['key_pair_name'], $regions);
    $this->drupalPostForm("/clouds/design/server_template/$cloud_context/1/launch",
      [],
      $this->t('Launch'));
    $this->assertNoErrorMessage();

    // Change security groups.
    $security_group_name1 = $this->random->name(8, TRUE);
    $security_group_name2 = $this->random->name(8, TRUE);
    $this->updateSecurityGroupsMockData($security_group_name1, $security_group_name2);

    // Change instance type.
    $instance_type = $this->random->name(6, TRUE);
    $this->updateInstanceTypeMockData($instance_type);

    // Update the schedule tag.
    $schedule_value = $this->random->name(8, TRUE);
    $this->updateScheduleTagMockData($schedule_value);

    // Run cron job to update instances.
    $key = \Drupal::state()->get('system.cron_key');
    $this->drupalGet('/cron/' . $key);
    $this->assertSession()->statusCodeEquals(204);

    // Verify schedule tag.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance/1");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains((string) $schedule_value);

    // Verify security group.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance/1");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains("$security_group_name1, $security_group_name2");
    $this->assertSession()->pageTextContains($instance_type);
  }

  /**
   * Test validation of launching instances.
   */
  public function testLaunchValidation(): void {
    try {
      $this->repeatTestLaunchValidation(self::AWS_CLOUD_INSTANCE_REPEAT_COUNT);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

  /**
   * Repeat testing validation of launching instances.
   *
   * @param int $max_test_repeat_count
   *   Max test repeating count.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Exception
   */
  private function repeatTestLaunchValidation($max_test_repeat_count = 1): void {
    $cloud_context = $this->cloudContext;

    // IAM Roles.
    $iam_roles = $this->createIamRolesRandomTestFormData();
    $this->updateIamRolesMockData($iam_roles);

    // Add an empty value to IAM roles.
    $iam_roles = array_merge([[]], $iam_roles);

    // Launch a new Instance, with termination protection.
    $add = $this->createInstanceTestFormData($max_test_repeat_count);
    for ($i = 0, $num = 1; $i < $max_test_repeat_count; $i++, $num++) {

      // Create image.
      $image = $this->createImageTestEntity(Image::class, $i, $add[$i]['image_id'], $cloud_context);

      // Make sure if the image entity is created or not.
      $this->drupalGet("/clouds/aws_cloud/${cloud_context}/image");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($image->getName());
      $this->assertSession()->pageTextContains($image->getImageId());
      $this->assertSession()->pageTextContains($add[$i]['image_id']);

      // Setup cloud server template and instance.
      $server_template = $this->createServerTemplateTestEntity($iam_roles, $image, $cloud_context);

      // Make sure if the cloud_server_template entity is created or not.
      $this->drupalGet("/clouds/design/server_template/${cloud_context}");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($server_template->name->value);

      $this->drupalPostForm("/clouds/design/server_template/$cloud_context/$num/launch",
        [
          'terminate' => '1',
          'termination_protection' => '1',
        ],
        $this->t('Launch')
      );

      $this->assertSession()->pageTextContains(
        $this->t('"@name1" and "@name2" can\'t be selected both. Please unselect one of them.',
          [
            '@name1' => $this->t('Termination Protection'),
            '@name2' => $this->t('Automatically terminate instance'),
          ]
        )
      );
    }
  }

  /**
   * Tests updating instances.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Exception
   */
  public function testUpdateInstanceList(): void {
    $cloud_context = $this->cloudContext;
    $regions = ['us-west-1', 'us-west-2'];

    // IAM Roles.
    $iam_roles = $this->createIamRolesRandomTestFormData();
    $this->updateIamRolesMockData($iam_roles);

    // Add an empty value to IAM roles.
    $iam_roles = array_merge([[]], $iam_roles);

    // Create image.
    $add = $this->createInstanceTestFormData(self::AWS_CLOUD_INSTANCE_REPEAT_COUNT);
    $image = $this->createImageTestEntity(Image::class, 0, $add[0]['image_id'], $cloud_context);

    // Make sure if the image entity is created or not.
    $this->drupalGet("/clouds/aws_cloud/${cloud_context}/image");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($image->getName());
    $this->assertSession()->pageTextContains($image->getImageId());
    $this->assertSession()->pageTextContains($add[0]['image_id']);

    // Setup cloud server template and instance.
    $server_template = $this->createServerTemplateTestEntity($iam_roles, $image, $cloud_context);

    // Make sure if the cloud_server_template entity is created or not.
    $this->drupalGet("/clouds/design/server_template/${cloud_context}");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($server_template->name->value);

    // Create Instances in mock data.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++, $num++) {
      $instance_id = $this->addInstanceMockData(InstanceTest::class, $add[$i]['name'], $add[$i]['key_pair_name'], $regions);
      $add[$i]['instance_id'] = $instance_id;
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Instances.'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Make sure detailed and edit view.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++, $num++) {

      // Confirm the detailed view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance/$num");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/instance/$num/edit");
      $this->assertSession()->linkNotExists($this->t('Start'));
      $this->assertSession()->linkExists($this->t('Stop'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/instance/$num/stop");
      $this->assertSession()->linkExists($this->t('Reboot'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/instance/$num/reboot");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/instance/$num/terminate");
      $this->assertSession()->linkNotExists($this->t('Associate Elastic IP'));
      $this->assertSession()->linkExists($this->t('List AWS Cloud Instances'));
      // Click 'Refresh'.
      $this->clickLink($this->t('List AWS Cloud Instances'));
      $this->assertNoErrorMessage();

      // Confirm the edit view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance/$num/edit");
      $this->assertSession()->linkNotExists($this->t('Edit'));
      $this->assertSession()->linkNotExists($this->t('Start'));
      $this->assertSession()->linkExists($this->t('Stop'));
      $this->assertSession()->linkExists($this->t('Reboot'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/instance/$num/reboot");
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/instance/$num/stop");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/instance/$num/terminate");
      $this->assertSession()->linkNotExists($this->t('Associate Elastic IP'));
    }

    // Edit Instance information.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++, $num++) {

      $this->createNetworkInterfaceTestEntity(NetworkInterface::class, $i, '', '', $add[$i]['instance_id']);
      $this->createElasticIpTestEntity($i);

      // Change Instance Name in mock data.
      $add[$i]['name'] = sprintf('instance-entity #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(8, TRUE));
      $this->updateInstanceMockData(InstanceTest::class, $i, $add[$i]['name'], $regions);

    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Instances.'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Make sure detailed and edit view.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++, $num++) {

      // Confirm the detailed view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance/$num");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/instance/$num/edit");
      $this->assertSession()->linkExists($this->t('Start'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/instance/$num/start");
      $this->assertSession()->linkNotExists($this->t('Stop'));
      $this->assertSession()->linkNotExists($this->t('Reboot'));
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/instance/$num/terminate");
      $this->assertSession()->linkExists($this->t('Associate Elastic IP'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/instance/$num/associate_elastic_ip");

      // Confirm the edit view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance/$num/edit");
      $this->assertSession()->linkNotExists($this->t('Edit'));
      $this->assertSession()->linkExists($this->t('Start'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/instance/$num/start");
      $this->assertSession()->linkNotExists($this->t('Stop'));
      $this->assertSession()->linkNotExists($this->t('Reboot'));
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/instance/$num/terminate");
      $this->assertSession()->linkExists($this->t('Associate Elastic IP'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/instance/$num/associate_elastic_ip");
    }

    // Update tags for empty.
    for ($i = 0; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateInstanceTagsInMockData($i, 'Name', '', FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance");
    for ($i = 0; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['instance_id']);
    }

    // Delete name tags.
    for ($i = 0; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateInstanceTagsInMockData($i, 'Name', '', TRUE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance");
    for ($i = 0; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['instance_id']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['instance_id']);
    }

    // Update tags.
    for ($i = 0; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++) {

      $this->updateInstanceTagsInMockData($i, 'Name', $add[$i]['name'], FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance");
    for ($i = 0; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Delete Instances in mock data.
    for ($i = 0; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++) {
      $this->deleteFirstInstanceMockData();
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Instances.'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Test creating an image from an instance.
   */
  public function testImageCreationFromInstance(): void {
    $this->repeatTestImageCreationFromInstance(self::AWS_CLOUD_INSTANCE_REPEAT_COUNT);

  }

  /**
   * Repeating test image creation from instance.
   *
   * @param int $max_test_repeat_count
   *   Max test repeating count.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  private function repeatTestImageCreationFromInstance($max_test_repeat_count = 1): void {
    $cloud_context = $this->cloudContext;
    $regions = ['us-west-1', 'us-west-2'];

    // IAM Roles.
    $iam_roles = $this->createIamRolesRandomTestFormData();
    $this->updateIamRolesMockData($iam_roles);

    // Add an empty value to IAM roles.
    $iam_roles = array_merge([[]], $iam_roles);

    // Launch a new Instance.
    $add = $this->createInstanceTestFormData($max_test_repeat_count);
    for ($i = 0, $num = 1; $i < $max_test_repeat_count; $i++, $num++) {

      // Create image.
      $image = $this->createImageTestEntity(Image::class, $i, $add[$i]['image_id'], $cloud_context);
      $this->addImageMockData($image->getImageId(), $image->getName(), $cloud_context);
      $this->updateImageCreationMockData($image->getImageId(), $image->getName(), 'available');

      // Make sure if the image entity is created or not.
      $this->drupalGet("/clouds/aws_cloud/${cloud_context}/image");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($image->getName());
      $this->assertSession()->pageTextContains($image->getImageId());
      $this->assertSession()->pageTextContains($add[$i]['image_id']);

      // Setup cloud server template and instance.
      $server_template = $this->createServerTemplateTestEntity($iam_roles, $image, $cloud_context);

      // Make sure if the cloud_server_template entity is created or not.
      $this->drupalGet("/clouds/design/server_template/${cloud_context}");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($server_template->name->value);

      $this->addInstanceMockData(InstanceTest::class, $add[$i]['name'], $add[$i]['key_pair_name'], $regions);
      $this->drupalPostForm("/clouds/design/server_template/$cloud_context/{$server_template->id()}/launch",
        [],
        $this->t('Launch'));

      $this->assertNoErrorMessage();

      // Make sure instances are available.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance/$num");
      $this->assertNoErrorMessage();

      // Update image mock data w/ the state 'available' to delete.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");

      // Delete the image used to create instance.
      $this->clickLink('Delete');
      $this->drupalPostForm($this->getUrl(),
        [],
        $this->t('Delete'));

      // Test image creation.
      $image_id = 'ami-' . $this->getRandomId();
      $image_name = $this->random->name(8, TRUE);

      $image_params = [
        'image_name' => $image_name,
        'no_reboot' => 0,
      ];

      // Update the mock data then create the image.
      $this->updateImageCreationMockData($image_id, $image_name, 'pending');
      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/instance/$num/create_image",
        $image_params,
        $this->t('Create Image'));

      $t_args = [
        '@type' => 'Instance',
        '%label' => $add[$i]['name'],
        '@image_id' => $image_id,
      ];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label (@image_id) has been created.', $t_args)));

      // Make sure the image was created.  Status should be pending.
      // Click on the Image link from the image listing page.
      $this->clickLink($image_name);
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($image_id);
      $this->assertSession()->pageTextContains('pending');

      // Go back to listing page. Make sure there is no delete link.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");
      $this->assertSession()->linkNotExists('Delete', $this->t('Cannot delete image in pending state'));

      // Update the image to 'available'.  Then delete the image.
      $this->updateImageCreationMockData($image_id, $image_name, 'available');

      // Run cron job to update images state.
      $key = \Drupal::state()->get('system.cron_key');
      $this->drupalGet('/cron/' . $key);
      $this->assertSession()->statusCodeEquals(204);

      // Go back into the main image.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");
      // Click into the image. Make sure the status is now available.
      $this->clickLink($image_name);
      $this->assertSession()->pageTextContains('available');

      // Go back to main listing page.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");

      // Delete the image.
      $this->clickLink('Delete');
      $this->drupalPostForm($this->getUrl(),
        [],
        $this->t('Delete'));
      $this->assertNoErrorMessage();

      // Make sure image is deleted.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");
      $this->assertSession()->pageTextNotContains($image_id);

      // Test "Failed" Image.  Failed Images should be allowed to be deleted.
      // Reset the image_id and image_name variables.
      $image_id = 'ami-' . $this->getRandomId();
      $image_name = $this->random->name(8, TRUE);

      $image_params = [
        'image_name' => $image_name,
        'no_reboot' => 0,
      ];

      // Update the image so it is in failed state.
      $this->updateImageCreationMockData($image_id, $image_name, 'failed');
      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/instance/$num/create_image",
        $image_params,
        $this->t('Create Image'));

      $t_args = [
        '@type' => 'Instance',
        '%label' => $add[$i]['name'],
        '@image_id' => $image_id,
      ];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label (@image_id) has been created.', $t_args)));

      // Go to the main image page.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");
      // Make sure the status is now failed.
      $this->clickLink($image_name);
      $this->assertSession()->pageTextContains('failed');

      // Go to the main image page.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");

      // Delete the Failed image.
      $this->clickLink('Delete');
      $this->drupalPostForm($this->getUrl(),
        [],
        $this->t('Delete'));
      $this->assertNoErrorMessage();

      // Make sure image is deleted.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");
      $this->assertSession()->pageTextNotContains($image_id);
    }
  }

}
