<?php

namespace Drupal\Tests\openstack\Functional\OpenStack;

use Drupal\openstack\Entity\OpenStackImage;
use Drupal\Tests\openstack\Functional\OpenStackTestBase;
use Drupal\Tests\cloud\Functional\Utils;

/**
 * Tests OpenStack Instance.
 *
 * @group OpenStack
 */
class OpenStackInstanceExtraTest extends OpenStackTestBase {

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
    $regions = ['us-west-1', 'us-west-2'];
    $region = $regions[array_rand($regions)];

    return [
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
      'state' => 'running',
    ];
  }

  /**
   * Tests updating instance attributes.
   */
  public function testUpdateInstanceAttributes(): void {
    try {
      $this->repeatTestUpdateInstanceAttributes(self::OPENSTACK_INSTANCE_REPEAT_COUNT);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

  /**
   * Repeating test update instance attributes.
   *
   * @param int $max_test_repeat_count
   *   Max test repeating count.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Exception
   */
  private function repeatTestUpdateInstanceAttributes($max_test_repeat_count = 1): void {
    $cloud_context = $this->cloudContext;
    $regions = ['RegionOne'];

    $add = $this->createOpenStackInstanceTestFormData($max_test_repeat_count, TRUE);
    $edit = $this->createOpenStackInstanceTestFormData($max_test_repeat_count, TRUE);
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

      // Launch a stopped Instance.
      $this->addInstanceMockData(OpenStackInstanceTest::class, $add[$i]['name'], $add[$i]['key_pair_name'], $regions, 'stopped');
      $this->drupalPostForm("/clouds/design/server_template/$cloud_context/$num/launch",
        [],
        $this->t('Launch'));

      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains('stopped');

      // Edit instance.
      unset(
        $edit[$i]['image_id'],
        $edit[$i]['image_name'],
        $edit[$i]['min_count'],
        $edit[$i]['max_count'],
        $edit[$i]['key_pair_name'],
        $edit[$i]['is_monitoring'],
        $edit[$i]['availability_zone'],
        $edit[$i]['kernel_id'],
        $edit[$i]['ramdisk_id'],
        $edit[$i]['instance_type']
      );

      // Change security groups.
      $security_groups = $this->createSecurityGroupRandomTestFormData();
      $this->updateDescribeSecurityGroupsMockData($security_groups);
      $edit[$i]['security_groups[]'] = array_column($security_groups, 'GroupName');

      $this->drupalPostForm("/clouds/openstack/$cloud_context/instance/$num/edit",
                            $edit[$i],
                            $this->t('Save'));

      $this->updateInstanceMockData(OpenStackInstanceTest::class, $i, $edit[$i]['name'], $regions);

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Instance', '%label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
      $t_args = ['@type' => 'Instance', '%label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The User Data of @type %label has been updated. Please start the @type to reflect the User Data.', $t_args)));

      // Verify instance attributes.
      $this->drupalGet("/clouds/openstack/$cloud_context/instance/$num");
      $this->assertNoErrorMessage();
      $groups = implode(', ', $edit[$i]['security_groups[]']);
      $this->assertSession()->pageTextContains($groups);
      $this->assertSession()->pageTextContains($edit[$i]['user_data']);
    }
  }

  /**
   * Tests setting the configuration of instance terminating.
   */
  public function testInstanceTerminateConfiguration(): void {
    try {
      $this->repeatTestInstanceTerminateConfiguration(self::OPENSTACK_INSTANCE_REPEAT_COUNT);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

  /**
   * Repeating test instance terminate configuration.
   *
   * @param int $max_test_repeat_count
   *   Max test repeating count.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Exception
   */
  private function repeatTestInstanceTerminateConfiguration($max_test_repeat_count = 1): void {
    $cloud_context = $this->cloudContext;

    $terminate_allowed_values = [TRUE, FALSE];
    $add = $this->createOpenStackInstanceTestFormData($max_test_repeat_count, TRUE);
    for ($i = 0, $num = 1; $i < $max_test_repeat_count; $i++, $num++) {

      $terminate_value = $terminate_allowed_values[array_rand($terminate_allowed_values)];
      $this->drupalPostForm('admin/config/services/cloud/openstack/settings',
        ['openstack_instance_terminate' => $terminate_value],
        $this->t('Save configuration')
      );
      $this->assertNoErrorMessage();

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

      $this->drupalGet("/clouds/design/server_template/$cloud_context/$num/launch");
      if ($terminate_value) {
        $this->assertSession()->checkboxChecked('edit-terminate');
      }
      else {
        $this->assertSession()->checkboxNotChecked('edit-terminate');
      }
    }
  }

}
