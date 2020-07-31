<?php

namespace Drupal\Tests\aws_cloud\Functional\Ec2;

use Drupal\aws_cloud\Entity\Ec2\Image;
use Drupal\Tests\aws_cloud\Functional\AwsCloudTestBase;
use Drupal\Tests\cloud\Functional\Utils;

/**
 * Tests AWS Cloud Instance.
 *
 * @group AWS Cloud
 */
class InstanceExtraTest extends AwsCloudTestBase {

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
      $this->repeatTestUpdateInstanceAttributes(self::AWS_CLOUD_INSTANCE_REPEAT_COUNT);
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
    $regions = ['us-west-1', 'us-west-2'];

    // IAM Roles.
    $iam_roles = $this->createIamRolesRandomTestFormData();
    $this->updateIamRolesMockData($iam_roles);

    // Add an empty value to IAM roles.
    $iam_roles = array_merge([[]], $iam_roles);

    $add = $this->createInstanceTestFormData($max_test_repeat_count);
    $edit = $this->createInstanceTestFormData($max_test_repeat_count);
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

      // Launch a stopped Instance.
      $this->addInstanceMockData(InstanceTest::class, $add[$i]['name'], $add[$i]['key_pair_name'], $regions, 'stopped');
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
        $edit[$i]['ramdisk_id']
      );

      $instance_type = 't3.small';
      $edit[$i]['instance_type'] = $instance_type;

      // Change security groups.
      $security_groups = $this->createSecurityGroupRandomTestFormData();
      $this->updateDescribeSecurityGroupsMockData($security_groups);
      $edit[$i]['security_groups[]'] = array_column($security_groups, 'GroupName');

      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/instance/$num/edit",
                            $edit[$i],
                            $this->t('Save'));

      $this->updateInstanceMockData(InstanceTest::class, $i, $edit[$i]['name'], $regions);

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Instance', '%label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
      $t_args = ['@type' => 'Instance', '%label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The User Data of @type %label has been updated. Please start the @type to reflect the User Data.', $t_args)));

      // Verify instance attributes.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance/$num");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($instance_type);
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
      $this->repeatTestInstanceTerminateConfiguration(self::AWS_CLOUD_INSTANCE_REPEAT_COUNT);
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

    // IAM Roles.
    $iam_roles = $this->createIamRolesRandomTestFormData();
    $this->updateIamRolesMockData($iam_roles);

    // Add an empty value to IAM roles.
    $iam_roles = array_merge([[]], $iam_roles);

    $terminate_allowed_values = [TRUE, FALSE];
    $add = $this->createInstanceTestFormData($max_test_repeat_count);
    for ($i = 0, $num = 1; $i < $max_test_repeat_count; $i++, $num++) {

      $terminate_value = $terminate_allowed_values[array_rand($terminate_allowed_values)];
      $this->drupalPostForm('admin/config/services/cloud/aws_cloud/settings',
        ['aws_cloud_instance_terminate' => $terminate_value],
        $this->t('Save configuration')
      );
      $this->assertNoErrorMessage();

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

      $this->drupalGet("/clouds/design/server_template/$cloud_context/$num/launch");
      if ($terminate_value) {
        $this->assertSession()->checkboxChecked('edit-terminate');
      }
      else {
        $this->assertSession()->checkboxNotChecked('edit-terminate');
      }
    }
  }

  /**
   * Test launching instances with schedule tags.
   */
  public function testCreateInstanceWithScheduleTag(): void {
    try {
      $this->repeatTestCreateInstanceWithScheduleTag(self::AWS_CLOUD_INSTANCE_REPEAT_COUNT);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

  /**
   * Repeat testing create instance with schedule tag.
   *
   * @param int $max_test_repeat_count
   *   Max test repeating count.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Exception
   */
  private function repeatTestCreateInstanceWithScheduleTag($max_test_repeat_count = 1): void {
    $cloud_context = $this->cloudContext;
    $regions = ['us-west-1', 'us-west-2'];

    // IAM Roles.
    $iam_roles = $this->createIamRolesRandomTestFormData();
    $this->updateIamRolesMockData($iam_roles);

    // Add an empty value to IAM roles.
    $iam_roles = array_merge([[]], $iam_roles);

    // Setup an arbitrary schedule in the configuration.
    // This is needed in the cloud server template launch confirmation form.
    $schedule_value = $this->random->name(8, TRUE);

    $config = \Drupal::configFactory()->getEditable('aws_cloud.settings');
    $config->set('aws_cloud_scheduler_periods', $schedule_value)
      ->save();

    // Launch a new Instance, with schedule information.
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

      $this->addInstanceMockData(InstanceTest::class, $add[$i]['name'], $add[$i]['key_pair_name'], $regions, 'running', $schedule_value);

      $this->drupalPostForm("/clouds/design/server_template/$cloud_context/$num/launch",
        ['schedule' => $schedule_value],
        $this->t('Launch'));

      $this->assertNoErrorMessage();

      // Go to the instance page.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance/$num");
      $this->assertSession()->pageTextContains($schedule_value);
    }
  }

}
