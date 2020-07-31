<?php

namespace Drupal\Tests\aws_cloud\Functional\Vpc;

use Drupal\Tests\aws_cloud\Functional\AwsCloudTestBase;

/**
 * Tests AWS Cloud Subnet.
 *
 * @group AWS Cloud
 */
class SubnetTest extends AwsCloudTestBase {

  public const AWS_CLOUD_SUBNET_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list aws cloud subnet',
      'add aws cloud subnet',
      'view any aws cloud subnet',
      'edit any aws cloud subnet',
      'delete any aws cloud subnet',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getMockDataTemplateVars(): array {
    return [
      'subnet_id' => 'subnet-' . $this->getRandomId(),
    ];
  }

  /**
   * Tests CRUD for Subnet information.
   *
   * @throws \Exception
   */
  public function testSubnet(): void {
    $cloud_context = $this->cloudContext;

    // List Subnet for Amazon EC2.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/subnet");
    $this->assertNoErrorMessage();

    $vpc_ids = $this->updateVpcsMockData(self::AWS_CLOUD_SUBNET_REPEAT_COUNT);

    // Add a new Subnet.
    $add = $this->createSubnetTestFormData(self::AWS_CLOUD_SUBNET_REPEAT_COUNT, $vpc_ids);
    for ($i = 0; $i < self::AWS_CLOUD_SUBNET_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $subnet_id = $this->latestTemplateVars['subnet_id'];
      $this->addSubnetMockData($add[$i], $subnet_id);

      $this->drupalPostForm(
        "/clouds/aws_cloud/$cloud_context/subnet/add",
        $add[$i],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Subnet', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/subnet");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);

      $add[$i]['subnet_id'] = $subnet_id;
    }

    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_SUBNET_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all subnet listing exists.
      $this->drupalGet('/clouds/aws_cloud/subnet');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Subnet.
    $edit = $this->createSubnetTestFormData(self::AWS_CLOUD_SUBNET_REPEAT_COUNT, $vpc_ids);
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_SUBNET_REPEAT_COUNT; $i++, $num++) {

      unset(
        $edit[$i]['vpc_id'],
        $edit[$i]['cidr_block']
      );

      $this->modifySubnetMockData($i, $edit[$i]);

      $this->drupalPostForm(
        "/clouds/aws_cloud/$cloud_context/subnet/$num/edit",
        $edit[$i],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Subnet', '%label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/subnet");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($edit[$i]['name']);
    }

    // Update tags for empty.
    for ($i = 0; $i < self::AWS_CLOUD_SUBNET_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'Subnets', 'Name', '', FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/subnet");
    for ($i = 0; $i < self::AWS_CLOUD_SUBNET_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($edit[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_SUBNET_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($edit[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['subnet_id']);
    }

    // Delete name tags.
    for ($i = 0; $i < self::AWS_CLOUD_SUBNET_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'Subnets', 'Name', '', TRUE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/subnet");
    for ($i = 0; $i < self::AWS_CLOUD_SUBNET_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($edit[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['subnet_id']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_SUBNET_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($edit[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['subnet_id']);
    }

    // Update tags.
    for ($i = 0; $i < self::AWS_CLOUD_SUBNET_REPEAT_COUNT; $i++) {

      $this->updateTagsInMockData($i, 'Subnets', 'Name', $edit[$i]['name'], FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/subnet");
    for ($i = 0; $i < self::AWS_CLOUD_SUBNET_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($edit[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['subnet_id']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_SUBNET_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($edit[$i]['name']);
    }

    // Delete Subnet.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_SUBNET_REPEAT_COUNT; $i++, $num++) {

      $this->deleteSubnetMockData($i);

      $this->drupalGet("/clouds/aws_cloud/$cloud_context/subnet/$num/delete");
      $this->assertNoErrorMessage();

      $this->drupalPostForm(
        "/clouds/aws_cloud/$cloud_context/subnet/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Subnet', '@label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/subnet");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($edit[$i]['name']);
    }
  }

  /**
   * Tests deleting subnets with bulk operation.
   *
   * @throws \Exception
   */
  public function testSubnetBulk(): void {
    $cloud_context = $this->cloudContext;
    for ($i = 0; $i < self::AWS_CLOUD_SUBNET_REPEAT_COUNT; $i++) {
      // Create Subnets.
      $subnets = $this->createSubnetsRandomTestFormData();
      $index = 0;
      $entities = [];
      foreach ($subnets ?: [] as $subnet) {
        $entities[] = $this->createSubnetTestEntity($index++, $subnet['SubnetId'], $subnet['Name'], $cloud_context);
      }
      $this->runTestEntityBulk('subnet', $entities);
    }
  }

}
