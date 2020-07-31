<?php

namespace Drupal\Tests\aws_cloud\Functional\Vpc;

use Drupal\Tests\aws_cloud\Functional\AwsCloudTestBase;

/**
 * Tests AWS Cloud VPC.
 *
 * @group AWS Cloud
 */
class VpcTest extends AwsCloudTestBase {

  public const AWS_CLOUD_VPC_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list aws cloud vpc',
      'add aws cloud vpc',
      'view any aws cloud vpc',
      'edit any aws cloud vpc',
      'delete any aws cloud vpc',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getMockDataTemplateVars(): array {
    return [
      'vpc_id' => 'vpc-' . $this->getRandomId(),
    ];
  }

  /**
   * Tests CRUD for VPC information.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Exception
   */
  public function testVpc(): void {
    $cloud_context = $this->cloudContext;

    // List VPC for Amazon EC2.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/vpc");
    $this->assertNoErrorMessage();

    // Add a new Vpc.
    $add = $this->createVpcTestFormData(self::AWS_CLOUD_VPC_REPEAT_COUNT);
    for ($i = 0; $i < self::AWS_CLOUD_VPC_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $vpc_id = $this->latestTemplateVars['vpc_id'];
      $this->addVpcMockData($add[$i], $vpc_id);

      $this->drupalPostForm(
        "/clouds/aws_cloud/$cloud_context/vpc/add",
        $add[$i],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'VPC', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/vpc");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);

      $add[$i]['vpc_id'] = $vpc_id;
    }

    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_VPC_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all vpc listing exists.
      $this->drupalGet('/clouds/aws_cloud/vpc');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Vpc.
    $edit = $this->createVpcTestFormData(self::AWS_CLOUD_VPC_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_VPC_REPEAT_COUNT; $i++, $num++) {

      unset($edit[$i]['cidr_block']);

      $this->modifyVpcMockData($i, $edit[$i]);

      $this->drupalPostForm(
        "/clouds/aws_cloud/$cloud_context/vpc/$num/edit",
        $edit[$i],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'VPC', '%label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/vpc");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($edit[$i]['name']);
    }

    // Update tags for empty.
    for ($i = 0; $i < self::AWS_CLOUD_VPC_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'Vpcs', 'Name', '', FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/vpc");
    for ($i = 0; $i < self::AWS_CLOUD_VPC_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($edit[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_VPC_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($edit[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['vpc_id']);
    }

    // Delete name tags.
    for ($i = 0; $i < self::AWS_CLOUD_VPC_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'Vpcs', 'Name', '', TRUE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/vpc");
    for ($i = 0; $i < self::AWS_CLOUD_VPC_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($edit[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['vpc_id']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_VPC_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($edit[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['vpc_id']);
    }

    // Update tags.
    for ($i = 0; $i < self::AWS_CLOUD_VPC_REPEAT_COUNT; $i++) {

      $this->updateTagsInMockData($i, 'Vpcs', 'Name', $edit[$i]['name'], FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/vpc");
    for ($i = 0; $i < self::AWS_CLOUD_VPC_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($edit[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['vpc_id']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_VPC_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($edit[$i]['name']);
    }

    // Delete Vpc.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_VPC_REPEAT_COUNT; $i++, $num++) {

      $this->deleteVpcMockData($i);

      $this->drupalGet("/clouds/aws_cloud/$cloud_context/vpc/$num/delete");
      $this->assertNoErrorMessage();

      $this->drupalPostForm(
        "/clouds/aws_cloud/$cloud_context/vpc/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'VPC', '@label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/vpc");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($edit[$i]['name']);
    }
  }

  /**
   * Tests deleting VPCs with bulk operation.
   *
   * @throws \Exception
   */
  public function testVpcBulk(): void {
    $cloud_context = $this->cloudContext;
    for ($i = 0; $i < self::AWS_CLOUD_VPC_REPEAT_COUNT; $i++) {
      // Create VPCs.
      $vpcs = $this->createVpcsRandomTestFormData();
      $index = 0;
      $entities = [];
      foreach ($vpcs ?: [] as $vpc) {
        $entities[] = $this->createVpcTestEntity($index++, $vpc['VpcId'], $vpc['Name'], $cloud_context);
      }
      $this->runTestEntityBulk('vpc', $entities);
    }
  }

}
