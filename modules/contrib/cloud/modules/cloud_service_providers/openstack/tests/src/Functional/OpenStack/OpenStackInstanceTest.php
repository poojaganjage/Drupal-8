<?php

namespace Drupal\Tests\openstack\Functional\OpenStack;

use Drupal\openstack\Entity\OpenStackImage;
use Drupal\openstack\Entity\OpenStackNetworkInterface;
use Drupal\Tests\openstack\Functional\OpenStackTestBase;
use Drupal\Tests\cloud\Functional\Utils;

/**
 * Tests OpenStack Instance for basic operations.
 *
 * @group OpenStack
 */
class OpenStackInstanceTest extends OpenStackTestBase {

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
   * Tests EC2 instance.
   *
   * @throws \Exception
   */
  public function testInstance(): void {
    $cloud_context = $this->cloudContext;
    $regions = ['RegionOne'];

    // List Instance for Amazon EC2.
    $this->drupalGet("/clouds/openstack/${cloud_context}/instance");
    $this->assertNoErrorMessage();

    // Launch a new Instance.
    $add = $this->createOpenStackInstanceTestFormData(self::OPENSTACK_INSTANCE_REPEAT_COUNT, TRUE);
    for ($i = 0, $num = 1; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++, $num++) {

      // Create image.
      $image = $this->createImageTestEntity(OpenStackImage::class, $i, $add[$i]['image_id'], $cloud_context);

      // Make sure if the image entity is created or not.
      $this->drupalGet("/clouds/openstack/${cloud_context}/image");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($image->getName());
      $this->assertSession()->pageTextContains($image->getImageId());
      $this->assertSession()->pageTextContains($add[$i]['image_id']);

      // Setup cloud server template and instance.
      $server_template = $this->createOpenStackServerTemplateTestEntity([], $image, $cloud_context, TRUE);

      // Make sure if the cloud_server_template entity is created or not.
      $this->drupalGet("/clouds/design/server_template/${cloud_context}");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($server_template->name->value);

      $this->addInstanceMockData(OpenStackInstanceTest::class, $add[$i]['name'], $add[$i]['key_pair_name'], $regions);
      $this->drupalPostForm("/clouds/design/server_template/${cloud_context}/{$server_template->id()}/launch",
        [],
        $this->t('Launch'));

      $this->assertNoErrorMessage();

      // Make sure listing.
      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    for ($i = 0, $num = 1; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all instance listing exists.
      $this->drupalGet('/clouds/openstack/instance');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit an Instance information.
    $edit = $this->createOpenStackInstanceTestFormData(self::OPENSTACK_INSTANCE_REPEAT_COUNT, TRUE);
    for ($i = 0, $num = 1; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++, $num++) {

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

      $this->drupalPostForm("/clouds/openstack/$cloud_context/instance/$num/edit",
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

      $this->drupalPostForm("/clouds/openstack/$cloud_context/instance/$num/edit",
                            $edit[$i],
                            $this->t('Save'));

      $this->updateInstanceMockData(OpenStackInstanceTest::class, $i, $edit[$i]['name'], $regions);

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Instance', '%label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/openstack/$cloud_context/instance");
      $this->assertNoErrorMessage();
      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($edit[$i]['name']);
      }
    }

    // Terminate Instance.
    for ($i = 0, $num = 1; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++, $num++) {

      $this->drupalGet("/clouds/openstack/$cloud_context/instance/$num/terminate");
      $this->assertNoErrorMessage();

      $this->deleteFirstInstanceMockData();

      $this->drupalPostForm("/clouds/openstack/$cloud_context/instance/$num/terminate",
                            [],
                            $this->t('Delete | Terminate'));

      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($edit[$i]['name']);
      $t_args = ['@type' => 'Instance', '@label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/openstack/$cloud_context/instance");
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
    $regions = ['RegionOne'];

    // Create image.
    $add = $this->createOpenStackInstanceTestFormData(1, TRUE);
    $image = $this->createImageTestEntity(OpenStackImage::class, 0, $add[0]['image_id'], $cloud_context);

    // Make sure if the image entity is created or not.
    $this->drupalGet("/clouds/openstack/${cloud_context}/image");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($image->getName());
    $this->assertSession()->pageTextContains($image->getImageId());
    $this->assertSession()->pageTextContains($add[0]['image_id']);

    // Setup cloud server template and instance.
    $server_template = $this->createOpenStackServerTemplateTestEntity([], $image, $cloud_context, TRUE);

    // Make sure if the cloud_server_template entity is created or not.
    $this->drupalGet("/clouds/design/server_template/${cloud_context}");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($server_template->name->value);

    // Launch a new Instance.
    $this->addInstanceMockData(OpenStackInstanceTest::class, $add[0]['name'], $add[0]['key_pair_name'], $regions);
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

    // Run cron job to update instances.
    $key = \Drupal::state()->get('system.cron_key');
    $this->drupalGet('/cron/' . $key);
    $this->assertSession()->statusCodeEquals(204);

    // Verify security group.
    $this->drupalGet("/clouds/openstack/$cloud_context/instance/1");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($security_group_name1 . ', ' . $security_group_name2);
    $this->assertSession()->pageTextContains($instance_type);
  }

  /**
   * Test validation of launching instances.
   */
  public function testLaunchValidation(): void {
    try {
      $this->repeatTestLaunchValidation(self::OPENSTACK_INSTANCE_REPEAT_COUNT);
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

    // Launch a new Instance, with termination protection.
    $add = $this->createOpenStackInstanceTestFormData($max_test_repeat_count, TRUE);
    for ($i = 0, $num = 1; $i < $max_test_repeat_count; $i++, $num++) {

      // Create image.
      $image = $this->createImageTestEntity(OpenStackImage::class, $i, $add[$i]['image_id'], $cloud_context);

      // Make sure if the image entity is created or not.
      $this->drupalGet("/clouds/openstack/${cloud_context}/image");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($image->getName());
      $this->assertSession()->pageTextContains($image->getImageId());
      $this->assertSession()->pageTextContains($add[$i]['image_id']);

      // Setup cloud server template and instance.
      $server_template = $this->createOpenStackServerTemplateTestEntity([], $image, $cloud_context, TRUE);

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
    $regions = ['RegionOne'];

    // Create image.
    $add = $this->createOpenStackInstanceTestFormData(self::OPENSTACK_INSTANCE_REPEAT_COUNT, TRUE);
    $image = $this->createImageTestEntity(OpenStackImage::class, 0, $add[0]['image_id'], $cloud_context);

    // Make sure if the image entity is created or not.
    $this->drupalGet("/clouds/openstack/${cloud_context}/image");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($image->getName());
    $this->assertSession()->pageTextContains($image->getImageId());
    $this->assertSession()->pageTextContains($add[0]['image_id']);

    // Setup cloud server template and instance.
    $server_template = $this->createOpenStackServerTemplateTestEntity([], $image, $cloud_context, TRUE);

    // Make sure if the cloud_server_template entity is created or not.
    $this->drupalGet("/clouds/design/server_template/${cloud_context}");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($server_template->name->value);

    // Create Instances in mock data.
    for ($i = 0, $num = 1; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++, $num++) {
      $instance_id = $this->addInstanceMockData(OpenStackInstanceTest::class, $add[$i]['name'], $add[$i]['key_pair_name'], $regions);
      $add[$i]['instance_id'] = $instance_id;
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/instance");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Instances.'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Make sure detailed and edit view.
    for ($i = 0, $num = 1; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++, $num++) {

      // Confirm the detailed view.
      $this->drupalGet("/clouds/openstack/$cloud_context/instance/$num");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/instance/$num/edit");
      $this->assertSession()->linkNotExists($this->t('Start'));
      $this->assertSession()->linkExists($this->t('Stop'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/instance/$num/stop");
      $this->assertSession()->linkExists($this->t('Reboot'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/instance/$num/reboot");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/instance/$num/terminate");
      $this->assertSession()->linkExists($this->t('List OpenStack Instances'));
      // Click 'Refresh'.
      $this->clickLink($this->t('List OpenStack Instances'));
      $this->assertNoErrorMessage();

      // Confirm the edit view.
      $this->drupalGet("/clouds/openstack/$cloud_context/instance/$num/edit");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/instance/$num/edit");
      $this->assertSession()->linkNotExists($this->t('Start'));
      $this->assertSession()->linkExists($this->t('Stop'));
      $this->assertSession()->linkExists($this->t('Reboot'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/instance/$num/reboot");
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/instance/$num/stop");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/instance/$num/terminate");
    }

    // Edit Instance information.
    for ($i = 0, $num = 1; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++, $num++) {

      $this->createNetworkInterfaceTestEntity(OpenStackNetworkInterface::class, $i, '', '', $add[$i]['instance_id']);
      $this->createElasticIpTestEntity($i);

      // Change Instance Name in mock data.
      $add[$i]['name'] = sprintf('instance-entity #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(8, TRUE));
      $this->updateInstanceMockData(OpenStackInstanceTest::class, $i, $add[$i]['name'], $regions);

    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/instance");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Instances.'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Make sure detailed and edit view.
    for ($i = 0, $num = 1; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++, $num++) {

      // Confirm the detailed view.
      $this->drupalGet("/clouds/openstack/$cloud_context/instance/$num");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/instance/$num/edit");
      $this->assertSession()->linkExists($this->t('Start'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/instance/$num/start");
      $this->assertSession()->linkNotExists($this->t('Stop'));
      $this->assertSession()->linkNotExists($this->t('Reboot'));
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/instance/$num/terminate");

      // Confirm the edit view.
      $this->drupalGet("/clouds/openstack/$cloud_context/instance/$num/edit");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/instance/$num/edit");
      $this->assertSession()->linkExists($this->t('Start'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/instance/$num/start");
      $this->assertSession()->linkNotExists($this->t('Stop'));
      $this->assertSession()->linkNotExists($this->t('Reboot'));
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/instance/$num/terminate");
    }

    // Update tags for empty.
    for ($i = 0; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateInstanceTagsInMockData($i, 'Name', '', FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/instance");
    for ($i = 0; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['instance_id']);
    }

    // Delete name tags.
    for ($i = 0; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateInstanceTagsInMockData($i, 'Name', '', TRUE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/instance");
    for ($i = 0; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['instance_id']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['instance_id']);
    }

    // Update tags.
    for ($i = 0; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++) {

      $this->updateInstanceTagsInMockData($i, 'Name', $add[$i]['name'], FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/instance");
    for ($i = 0; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Delete Instances in mock data.
    for ($i = 0; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++) {
      $this->deleteFirstInstanceMockData();
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/instance");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Instances.'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_INSTANCE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

}
