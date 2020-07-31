<?php

namespace Drupal\Tests\aws_cloud\Functional\Ec2;

use Drupal\aws_cloud\Entity\Ec2\NetworkInterface;
use Drupal\aws_cloud\Entity\Ec2\SecurityGroup;
use Drupal\Tests\aws_cloud\Functional\AwsCloudTestBase;

/**
 * Tests AWS Cloud Network Interface.
 *
 * @group AWS Cloud
 */
class NetworkInterfaceTest extends AwsCloudTestBase {

  public const AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list aws cloud network interface',
      'add aws cloud network interface',
      'view any aws cloud network interface',
      'edit any aws cloud network interface',
      'delete any aws cloud network interface',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getMockDataTemplateVars(): array {
    return [
      'network_interface_id' => 'eni-' . $this->getRandomId(),
      'vpc_id' => 'vpc-' . $this->getRandomId(),
    ];
  }

  /**
   * Tests CRUD for Network Interface information.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Exception
   */
  public function testNetworkInterface(): void {
    $cloud_context = $this->cloudContext;

    // List Network Interface for Amazon EC2.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/network_interface");
    $this->assertNoErrorMessage();

    // Create security groups.
    $security_groups = $this->createSecurityGroupRandomTestFormData();
    $index = 0;
    foreach ($security_groups ?: [] as $security_group) {
      $this->createSecurityGroupTestEntity(SecurityGroup::class, $index++, $security_group['GroupId'], $security_group['Name'], '', $cloud_context);
    }

    // Add a new Network Interface.
    $add = $this->createNetworkInterfaceTestFormData(self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT);
    $subnets = $this->createRandomSubnets();
    $this->updateSubnetsToMockData($subnets);
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addNetworkInterfaceMockData($add[$i]);

      unset($add[$i]['primary_private_ip']);

      $subnet_index = array_rand($subnets);
      $add[$i]['subnet_id'] = $subnets[$subnet_index]['SubnetId'];
      $security_group_index = array_rand($security_groups);
      $add[$i]['security_groups[]'] = $security_groups[$security_group_index]['GroupId'];

      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/network_interface/add",
                            $add[$i],
                            $this->t('Save'));
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains(
        $this->t('Network Interface @name', [
          '@name' => $add[$i]['name'],
        ])
      );
      $this->assertSession()->pageTextContains(
        $add[$i]['name']
      );

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/network_interface");
      $this->assertNoErrorMessage();
      for ($j = 0; $j < $i + 1; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all network_interface listing exists.
      $this->drupalGet('/clouds/aws_cloud/network_interface');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit an Network Interface information.
    $edit = $this->createNetworkInterfaceTestFormData(self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++, $num++) {

      unset(
        $edit[$i]['subnet_id'],
        $edit[$i]['security_groups[]'],
        $edit[$i]['primary_private_ip']
      );

      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/network_interface/$num/edit",
                            $edit[$i],
                            $this->t('Save'));
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Network Interface', '%label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      // Make sure the description.
      $this->assertSession()->fieldValueEquals('description', $edit[$i]['description']);

      $this->drupalGet("/clouds/aws_cloud/$cloud_context/network_interface");
      $this->assertNoErrorMessage();

      $this->assertSession()->pageTextContains($edit[$i]['name']);

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/network_interface");
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($edit[$i]['name']);
      }
    }

    // Delete Network Interface.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++, $num++) {

      $this->drupalGet("/clouds/aws_cloud/$cloud_context/network_interface/$num/delete");
      $this->assertNoErrorMessage();
      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/network_interface/$num/delete",
                            [],
                            $this->t('Delete'));

      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($edit[$i]['name']);
      $t_args = ['@type' => 'Network Interface', '@label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/network_interface");
      $this->assertNoErrorMessage();
    }
  }

  /**
   * Tests deleting network interfaces with bulk operation.
   *
   * @throws \Exception
   */
  public function testNetworkInterfaceBulk(): void {

    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {
      // Create network interface.
      $network_interfaces = $this->createNetworkInterfacesRandomTestFormData();
      $index = 0;
      $entities = [];
      foreach ($network_interfaces ?: [] as $network_interface) {
        $entities[] = $this->createNetworkInterfaceTestEntity(NetworkInterface::class, $index++, $network_interface['NetworkInterfaceId'], $network_interface['Name']);
      }

      $this->runTestEntityBulk('network_interface', $entities);
    }
  }

  /**
   * Test updating network interface.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testUpdateNetworkInterfaceList(): void {
    $cloud_context = $this->cloudContext;

    // Add a new Network Interface.
    $add = $this->createNetworkInterfaceTestFormData(self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT);
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addNetworkInterfaceMockData($add[$i]);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/network_interface");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Network Interfaces.'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Make sure detailed and edit view.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++, $num++) {

      // Confirm the detailed view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/network_interface/$num");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/network_interface/$num/edit");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/network_interface/$num/delete");
      $this->assertSession()->linkExists($this->t('List AWS Cloud Network Interfaces'));
      // Click 'Refresh'.
      $this->clickLink($this->t('List AWS Cloud Network Interfaces'));
      $this->assertNoErrorMessage();

      // Confirm the edit view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/network_interface/$num/edit");
      $this->assertSession()->linkNotExists($this->t('Edit'));
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/network_interface/$num/delete");
      $this->assertSession()->linkNotExists('Edit');
    }

    // Edit Network Interface information.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++, $num++) {

      // Change Network Interface Name in mock data.
      $add[$i]['name'] = 'eni-' . $this->getRandomId();
      $this->updateNetworkInterfaceMockData($num - 1, $add[$i]['name']);

    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/network_interface");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Network Interfaces.'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Update Network Interface tags.
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {

      // Update tags.
      $add[$i]['tags_name'] = $this->getRandomId();
      $this->updateTagsInMockData($i, 'NetworkInterfaces', 'Name', $add[$i]['tags_name'], FALSE, 'TagSet');
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/network_interface");
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['tags_name']);
    }

    // Update Network Interface tags for empty.
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'NetworkInterfaces', 'Name', '', FALSE, 'TagSet');
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/network_interface");
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['tags_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Delete Network Interface tags.
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'NetworkInterfaces', 'Name', '', TRUE, 'TagSet');
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/network_interface");
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Delete Network Interface in mock data.
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {
      $this->deleteFirstNetworkInterfaceMockData();
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/network_interface");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Network Interfaces.'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_NETWORK_INTERFACE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }

  }

}
