<?php

namespace Drupal\Tests\aws_cloud\Functional\Ec2;

use Drupal\aws_cloud\Entity\Ec2\Instance;
use Drupal\aws_cloud\Entity\Ec2\NetworkInterface;
use Drupal\Tests\aws_cloud\Functional\AwsCloudTestBase;
use Drupal\Tests\cloud\Functional\Utils;

/**
 * Tests AWS Cloud Elastic IP.
 *
 * @group AWS Cloud
 */
class ElasticIpTest extends AwsCloudTestBase {

  public const AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list aws cloud elastic ip',
      'add aws cloud elastic ip',
      'view any aws cloud elastic ip',
      'edit any aws cloud elastic ip',
      'delete any aws cloud elastic ip',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getMockDataTemplateVars(): array {
    return [
      // For Elastic IP.
      'public_ip' => Utils::getRandomPublicIp(),
      'allocation_id' => 'eipalloc-' . $this->getRandomId(),
      'domain' => 'vpc',

      // For Instance.
      'instance_id' => 'i-' . $this->getRandomId(),

      // For NetworkInterface.
      'network_interface_id' => 'eni-' . $this->getRandomId(),
      'vpc_id' => 'vpc-' . $this->getRandomId(),
      'description' => 'description-' . $this->random->name(64, TRUE),
      'subnet_id' => 'subnet_id-' . $this->getRandomId(),
      'is_primary' => TRUE,
      'primary_private_ip' => Utils::getRandomPrivateIp(),
      'secondary_private_ip' => Utils::getRandomPrivateIp(),
      'attachment_id' => 'attachment-' . $this->getRandomId(),
    ];
  }

  /**
   * Tests CRUD for Elastic IP information.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Exception
   */
  public function testElasticIp(): void {
    $cloud_context = $this->cloudContext;
    $regions = ['us-west-1', 'us-west-2'];

    // List Elastic IP for Amazon EC2.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip");
    $this->assertNoErrorMessage();

    $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip/add");

    // Add a new Elastic IP.
    $add = $this->createElasticIpTestFormData(self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++, $num++) {
      $this->reloadMockData();
      $domain = $this->getRandomDomain();
      $this->updateDomainMockData($domain);

      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/elastic_ip/add",
                            $add[$i],
                            $this->t('Save'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Elastic IP', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip/$num");
      $this->assertNoErrorMessage();

      // Make sure domain is updated.
      $this->assertSession()->pageTextContains($domain);

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip");
      $this->assertNoErrorMessage();
      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all elastic_ip listing exists.
      $this->drupalGet('/clouds/aws_cloud/elastic_ip');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit an Elastic IP information.
    $edit = $this->createElasticIpTestFormData(self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++, $num++) {

      unset($edit[$i]['domain']);

      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/elastic_ip/$num/edit",
                            $edit[$i],
                            $this->t('Save'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Elastic IP', '%label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip");
      $this->assertNoErrorMessage();
      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($edit[$j]['name']);
      }
    }

    // Delete Elastic IP
    // 3 times.
    $this->updateInstanceMockData(InstanceTest::class, 0, '', $regions);
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++, $num++) {

      $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip/$num/delete");
      $this->assertNoErrorMessage();
      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/elastic_ip/$num/delete",
                            [],
                            $this->t('Delete'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Elastic IP', '@label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip");
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextNotContains($edit[$j]['name']);
      }
    }
  }

  /**
   * Tests deleting Elastic IPs with bulk delete operation.
   *
   * @throws \Exception
   */
  public function testElasticIpBulkDelete(): void {

    $cloud_context = $this->cloudContext;
    $regions = ['us-west-1', 'us-west-2'];
    $this->deleteAllElasticIpInMockData();

    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {

      // Create Elastic IPs.
      $elastic_ips = $this->createElasticIpRandomTestFormData();
      $elastic_ips_count = count($elastic_ips);
      $network_interface_data = $this->createNetworkInterfaceTestFormData($elastic_ips_count, TRUE);

      for ($j = 0; $j < $elastic_ips_count; $j++) {

        $elastic_ip = $elastic_ips[$j];

        // Setup a test instance.
        $instance_id = 'i-' . $this->getRandomId();
        $instance = $this->createInstanceTestEntity(Instance::class, $j, $regions, $elastic_ip['PublicIp'], $instance_id);
        $instance_id = $this->addInstanceMockData(InstanceTest::class, $instance->getName(), $instance->getKeyPairName(), $regions);

        // Setup a test network interface.
        $this->createNetworkInterfaceTestEntity(NetworkInterface::class, $j, '', '', $instance_id);
        $this->addNetworkInterfaceMockData($network_interface_data[$j]);

        // Setup a test Elastic IP.
        $this->createElasticIpTestEntity($j, $elastic_ip['Name'], $elastic_ip['PublicIp'], $cloud_context);
        $this->addElasticIpMockData($elastic_ip['Name'], $elastic_ip['PublicIp'], 'standard');
      }

      $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip");

      $data = [];
      $data['action'] = 'aws_cloud_elastic_ip_delete_action';

      $checkboxes = $this->cssSelect('input[type=checkbox]');
      foreach ($checkboxes ?: [] as $checkbox) {
        if ($checkbox->getAttribute('name') === NULL) {
          continue;
        }

        $data[$checkbox->getAttribute('name')] = $checkbox->getAttribute('value');
      }

      // Confirm.
      $this->drupalPostForm(
        "/clouds/aws_cloud/$cloud_context/elastic_ip",
        $data,
        $this->t('Apply to selected items')
      );
      $this->assertNoErrorMessage();

      // Al lower case of Elastic IP is correct since the original @label linked
      // \Drupal\core\Entity\EntityType::getSingularLabel makes the string
      // lowercase.
      $message = 'Are you sure you want to delete these Elastic IPs?';
      if ($elastic_ips_count === 1) {
        $message = 'Are you sure you want to delete this Elastic IP?';
      }
      $this->assertSession()->pageTextContains($message);

      foreach ($elastic_ips ?: [] as $elastic_ip) {
        $this->assertSession()->pageTextContains($elastic_ip['Name']);
      }

      // Delete.
      $this->drupalPostForm(
        "/clouds/aws_cloud/$cloud_context/elastic_ip/delete_multiple",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();

      if ($elastic_ips_count === 1) {
        $this->assertSession()->pageTextContains("Deleted $elastic_ips_count Elastic IP.");
      }
      else {
        $this->assertSession()->pageTextContains("Deleted $elastic_ips_count Elastic IPs.");
      }

      foreach ($elastic_ips ?: [] as $elastic_ip) {
        $t_args = ['@type' => 'Elastic IP', '@label' => $elastic_ip['Name']];
        $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));
        $this->deleteFirstElasticIpMockData();
      }

      // Click 'Refresh'.
      $this->clickLink($this->t('Refresh'));
      $this->assertSession()->pageTextContains($this->t('Updated Elastic IPs and Network Interfaces.'));

      foreach ($elastic_ips ?: [] as $elastic_ip) {
        $name = $elastic_ip['Name'];
        $this->assertSession()->pageTextNotContains($name);
      }
    }
  }

  /**
   * Tests disassociating Elastic IPs with bulk operation.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testElasticIpBulkDisassociate(): void {

    $cloud_context = $this->cloudContext;
    $regions = ['us-west-1', 'us-west-2'];
    $this->deleteFirstElasticIpMockData();

    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {

      // Create Elastic IPs.
      $elastic_ips = $this->createElasticIpRandomTestFormData();
      $elastic_ips_count = count($elastic_ips);
      $network_interface_data = $this->createNetworkInterfaceTestFormData($elastic_ips_count, TRUE);

      for ($j = 0; $j < $elastic_ips_count; $j++) {

        $elastic_ip = $elastic_ips[$j];

        // Setup a test instance.
        $instance_id = 'i-' . $this->getRandomId();
        $instance = $this->createInstanceTestEntity(Instance::class, $j, $regions, $elastic_ip['PublicIp'], $instance_id);
        $this->addInstanceMockData(InstanceTest::class, $instance->getName(), $instance->getKeyPairName(), $regions);

        // Setup a test network interface.
        $this->createNetworkInterfaceTestEntity(NetworkInterface::class, $j, '', '', $instance_id);
        $this->addNetworkInterfaceMockData($network_interface_data[$j]);

        // Setup a test Elastic IP.
        // Associate EIP to the instance.
        $elastic_ips[$j]['InstanceId'] = $instance_id;
        $eip = $this->createElasticIpTestEntity($j, $elastic_ip['Name'], $elastic_ip['PublicIp'], $cloud_context);
        $this->addElasticIpMockData($elastic_ip['Name'], $elastic_ip['PublicIp'], 'standard');

        // Associate Elastic IP in mock data.
        $association_id = $this->random->name(8, TRUE);
        $eip->setAssociationId($association_id);
        $eip->setAllocationId($elastic_ip['Name']);
        $eip->setInstanceId($instance_id);
        $eip->save();
        $this->updateElasticIpMockData($j, $elastic_ip['Name'], $association_id, $instance_id);
      }

      $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip");

      $data = [];
      $data['action'] = 'aws_cloud_elastic_ip_disassociate_action';

      $checkboxes = $this->cssSelect('input[type=checkbox]');
      foreach ($checkboxes ?: [] as $checkbox) {
        if ($checkbox->getAttribute('name') === NULL) {
          continue;
        }

        $data[$checkbox->getAttribute('name')] = $checkbox->getAttribute('value');
      }

      // Confirm.
      $this->drupalPostForm(
        "/clouds/aws_cloud/$cloud_context/elastic_ip",
        $data,
        $this->t('Apply to selected items')
      );
      // @FIXME: Display error messages like No access to execute Disassociate
      // Elastic IP(s) on the Elastic IP.
      $this->assertSession()->statusCodeEquals(200);
      $this->assertSession()->pageTextNotContains($this->t('Warning message'));

      $message = 'Are you sure you want to disassociate these Elastic IPs?';
      if ($elastic_ips_count === 1) {
        $message = 'Are you sure you want to disassociate this Elastic IP?';
      }
      $this->assertSession()->pageTextContains($message);

      foreach ($elastic_ips ?: [] as $elastic_ip) {
        $this->assertSession()->pageTextContains($elastic_ip['Name']);
      }

      // Disassociate.
      $this->drupalPostForm(
        "/clouds/aws_cloud/$cloud_context/elastic_ip/disassociate_multiple",
        [],
        $this->t('Disassociate')
      );

      $this->assertNoErrorMessage();
      if ($elastic_ips_count === 1) {
        $this->assertSession()->pageTextContains("Disassociated $elastic_ips_count Elastic IP.");
      }
      else {
        $this->assertSession()->pageTextContains("Disassociated $elastic_ips_count Elastic IPs.");
      }

      for ($j = 0; $j < $elastic_ips_count; $j++) {
        $elastic_ip = $elastic_ips[$j];
        $t_args = ['@type' => 'Elastic IP', '%label' => $elastic_ip['Name']];
        $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been disassociated.', $t_args)));
        $this->updateElasticIpMockData($j, $elastic_ip['Name'], NULL, NULL);
      }

      // Click 'Refresh'.
      $this->clickLink($this->t('Refresh'));
      $this->assertSession()->pageTextContains($this->t('Updated Elastic IPs and Network Interfaces.'));

      // Make sure if disassociated from an instance.
      foreach ($elastic_ips ?: [] as $elastic_ip) {
        $this->assertSession()->pageTextNotContains($elastic_ip['InstanceId']);
      }
    }
  }

  /**
   * Test updating Elastic IPs.
   *
   * @throws \Exception
   */
  public function testUpdateElasticIpList(): void {

    $cloud_context = $this->cloudContext;
    $this->deleteAllElasticIpInMockData();

    // Add a new Elastic IP.
    $add = $this->createElasticIpTestFormData(self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT);
    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {
      $add[$i]['public_ip'] = Utils::getRandomPublicIp();
      $this->addElasticIpMockData($add[$i]['name'], $add[$i]['public_ip'], $add[$i]['domain']);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Elastic IPs and Network Interfaces.'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Make sure detailed and edit view.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++, $num++) {

      // Confirm the detailed view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip/$num");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/elastic_ip/$num/edit");
      $this->assertSession()->linkExists($this->t('Associate Elastic IP'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/elastic_ip/$num/associate");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/elastic_ip/$num/delete");
      $this->assertSession()->linkExists($this->t('List AWS Cloud Elastic IPs'));
      // Click 'Refresh'.
      $this->clickLink($this->t('List AWS Cloud Elastic IPs'));
      $this->assertNoErrorMessage();

      // Confirm the edit view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip/$num/edit");
      $this->assertSession()->linkNotExists($this->t('Edit'));
      $this->assertSession()->linkExists($this->t('Associate Elastic IP'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/elastic_ip/$num/associate");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/elastic_ip/$num/delete");
    }

    // Edit Elastic IP information.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++, $num++) {

      // Setup a test instance.
      $instance_id = 'i-' . $this->getRandomId();

      // Change Elastic IP Name in mock data.
      $add[$i]['name'] = sprintf('eip-entity #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE));
      $add[$i]['association_id'] = $this->random->name(8, TRUE);
      $this->updateElasticIpMockData($i, $add[$i]['name'], $add[$i]['association_id'], $instance_id);

    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Elastic IPs and Network Interfaces.'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Make sure detailed and edit view.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++, $num++) {

      // Confirm the detailed view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip/$num");
      $this->assertSession()->linkExists($this->t('Disassociate'));

      // Confirm the edit view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip/$num/edit");
      $this->assertSession()->linkExists($this->t('Disassociate Elastic IP'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/elastic_ip/$num/disassociate");
    }

    // Update Elastic IP tags.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++, $num++) {

      // Update tags.
      $add[$i]['tags_name'] = $this->getRandomId();
      $this->updateTagsInMockData($num - 1, 'Addresses', 'Name', $add[$i]['tags_name'], FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip");
    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['tags_name']);
    }

    // Update Elastic IP tags for empty.
    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'Addresses', 'Name', '', FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip");
    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['tags_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['public_ip']);
    }

    // Delete Elastic IP tags.
    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'Addresses', 'Name', '', TRUE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip");
    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['public_ip']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['public_ip']);
    }

    // Delete Elastic IP in mock data.
    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {
      $this->deleteFirstElasticIpMockData();
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/elastic_ip");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Elastic IPs and Network Interfaces.'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_ELASTIC_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

}
