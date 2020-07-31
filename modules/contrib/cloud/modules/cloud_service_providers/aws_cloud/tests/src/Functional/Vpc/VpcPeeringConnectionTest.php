<?php

namespace Drupal\Tests\aws_cloud\Functional\Vpc;

use Drupal\Tests\aws_cloud\Functional\AwsCloudTestBase;

/**
 * Tests AWS Cloud VPC Peering Connection.
 *
 * @group AWS Cloud
 */
class VpcPeeringConnectionTest extends AwsCloudTestBase {

  public const AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list aws cloud vpc peering connection',
      'add aws cloud vpc peering connection',
      'view any aws cloud vpc peering connection',
      'edit any aws cloud vpc peering connection',
      'delete any aws cloud vpc peering connection',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getMockDataTemplateVars(): array {
    return [
      'vpc_peering_connection_id' => 'pcx' . $this->getRandomId(),
    ];
  }

  /**
   * Tests CRUD for VPC Peering Connection information.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Exception
   */
  public function testVpcPeeringConnection(): void {
    $cloud_context = $this->cloudContext;

    // List VPC Peering Connection for Amazon EC2.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/vpc_peering_connection");
    $this->assertResponse(200);

    $vpc_ids = $this->updateVpcsMockData(self::AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT);

    // Add a new Vpc Peering Connection.
    $add = $this->createVpcPeeringConnectionTestFormData(self::AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT, $vpc_ids);
    for ($i = 0; $i < self::AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $vpc_peering_connection_id = $this->latestTemplateVars['vpc_peering_connection_id'];
      $this->addVpcPeeringConnectionMockData($add[$i], $vpc_peering_connection_id);

      $this->drupalPostForm(
        "/clouds/aws_cloud/$cloud_context/vpc_peering_connection/add",
        $add[$i],
        $this->t('Save')
      );
      $this->assertResponse(200);
      $t_args = ['@type' => 'VPC Peering Connection', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/vpc_peering_connection");
      $this->assertResponse(200);
      $this->assertSession()->pageTextContains($add[$i]['name']);

      $add[$i]['vpc_peering_connection_id'] = $vpc_peering_connection_id;

      // Accept a Vpc Peering Connection.
      $num = $i + 1;
      $this->drupalPostForm(
        "/clouds/aws_cloud/$cloud_context/vpc_peering_connection/$num/accept",
        [],
        $this->t('Accept')
      );
      $this->assertResponse(200);
      $t_args = ['@type' => 'VPC Peering Connection', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been accepted.', $t_args)));
    }

    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all vpc_peering_connection listing exists.
      $this->drupalGet('/clouds/aws_cloud/vpc_peering_connection');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Vpc Peering Connection.
    $edit = $this->createVpcPeeringConnectionTestFormData(self::AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT, $vpc_ids);
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT; $i++, $num++) {

      unset(
        $edit[$i]['requester_vpc_id'],
        $edit[$i]['accepter_vpc_id']
      );

      $this->modifyVpcPeeringConnectionMockData($i, $edit[$i]);

      $this->drupalPostForm(
        "/clouds/aws_cloud/$cloud_context/vpc_peering_connection/$num/edit",
        $edit[$i],
        $this->t('Save')
      );
      $this->assertResponse(200);
      $t_args = ['@type' => 'VPC Peering Connection', '%label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/vpc_peering_connection");
      $this->assertResponse(200);
      $this->assertSession()->pageTextContains($edit[$i]['name']);
    }

    // Update tags for empty.
    for ($i = 0; $i < self::AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'VpcPeeringConnections', 'Name', '', FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/vpc_peering_connection");
    for ($i = 0; $i < self::AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($edit[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($edit[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['vpc_peering_connection_id']);
    }

    // Delete name tags.
    for ($i = 0; $i < self::AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'VpcPeeringConnections', 'Name', '', TRUE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/vpc_peering_connection");
    for ($i = 0; $i < self::AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($edit[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['vpc_peering_connection_id']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($edit[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['vpc_peering_connection_id']);
    }

    // Update tags.
    for ($i = 0; $i < self::AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT; $i++) {

      $this->updateTagsInMockData($i, 'VpcPeeringConnections', 'Name', $edit[$i]['name'], FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/vpc_peering_connection");
    for ($i = 0; $i < self::AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($edit[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['vpc_peering_connection_id']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($edit[$i]['name']);
    }

    // Delete Vpc Peering Connection.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT; $i++, $num++) {

      $this->deleteVpcPeeringConnectionMockData($i);

      $this->drupalGet("/clouds/aws_cloud/$cloud_context/vpc_peering_connection/$num/delete");
      $this->assertResponse(200);

      $this->drupalPostForm(
        "/clouds/aws_cloud/$cloud_context/vpc_peering_connection/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertResponse(200);
      $t_args = ['@type' => 'VPC Peering Connection', '@label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/vpc_peering_connection");
      $this->assertResponse(200);
      $this->assertSession()->pageTextNotContains($edit[$i]['name']);
    }
  }

  /**
   * Tests deleting VPC Peering Connections with bulk operation.
   *
   * @throws \Exception
   */
  public function testVpcPeeringConnectionBulk(): void {
    $cloud_context = $this->cloudContext;
    for ($i = 0; $i < self::AWS_CLOUD_VPC_PEERING_CONNECTION_REPEAT_COUNT; $i++) {
      // Create VPC Peering Connections.
      $vpc_peering_connections = $this->createVpcPeeringConnectionsRandomTestFormData();
      $index = 0;
      $entities = [];
      foreach ($vpc_peering_connections as $vpc_peering_connection) {
        $entities[] = $this->createVpcPeeringConnectionTestEntity(
          $index++,
          $vpc_peering_connection['VpcPeeringConnectionId'],
          $vpc_peering_connection['Name'],
          $cloud_context
        );
      }
      $this->runTestEntityBulk('vpc_peering_connection', $entities);
    }
  }

}
