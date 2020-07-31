<?php

namespace Drupal\Tests\openstack\Functional\OpenStack;

use Drupal\openstack\Entity\OpenStackInstance;
use Drupal\openstack\Entity\OpenStackNetworkInterface;
use Drupal\Tests\openstack\Functional\OpenStackTestBase;
use Drupal\Tests\cloud\Functional\Utils;

/**
 * Tests OpenStack Floating IP.
 *
 * @group OpenStack
 */
class OpenStackFloatingIpTest extends OpenStackTestBase {

  public const OPENSTACK_FLOATING_IP_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list openstack floating ip',
      'add openstack floating ip',
      'view any openstack floating ip',
      'edit any openstack floating ip',
      'delete any openstack floating ip',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getMockDataTemplateVars(): array {
    return [
      // For Floating IP.
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
   * Tests CRUD for Floating IP information.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Exception
   */
  public function testFloatingIp(): void {
    $cloud_context = $this->cloudContext;
    $regions = ['RegionOne'];

    // List Floating IP for OpenStack.
    $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip");
    $this->assertNoErrorMessage();

    $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip/add");

    // Add a new Floating IP.
    $add = $this->createElasticIpTestFormData(self::OPENSTACK_FLOATING_IP_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++, $num++) {
      $this->reloadMockData();
      $domain = $this->getRandomDomain();
      $this->updateDomainMockData($domain);

      $this->drupalPostForm("/clouds/openstack/$cloud_context/floating_ip/add",
                            $add[$i],
                            $this->t('Save'));

      $add_mock_data = $this->getMockDataFromConfig();
      $add_mock_public_ip = $add_mock_data['AllocateAddress']['PublicIp'];

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Floating IP', '%label' => $add_mock_public_ip];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip/$num");
      $this->assertNoErrorMessage();

      // Make sure domain is updated.
      $this->assertSession()->pageTextContains($domain);

      // Make sure listing.
      $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip");
      $this->assertNoErrorMessage();
      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add_mock_public_ip);
      }
    }

    for ($i = 0, $num = 1; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all floating_ip listing exists.
      $this->drupalGet('/clouds/openstack/floating_ip');
      $this->assertNoErrorMessage();

      $mock_data = $this->getMockDataFromConfig();
      $mock_public_ip = $mock_data['AllocateAddress']['PublicIp'];

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($mock_public_ip);
      }
    }

    // Edit an Floating IP information.
    $edit = $this->createElasticIpTestFormData(self::OPENSTACK_FLOATING_IP_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++, $num++) {

      unset($edit[$i]['domain']);

      $this->drupalPostForm("/clouds/openstack/$cloud_context/floating_ip/$num/edit",
                            $edit[$i],
                            $this->t('Save'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Floating IP', '%label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip");
      $this->assertNoErrorMessage();
      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($edit[$j]['name']);
      }
    }

    // Delete Floating IP
    // 3 times.
    $this->updateInstanceMockData(OpenStackInstanceTest::class, 0, '', $regions);
    for ($i = 0, $num = 1; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++, $num++) {

      $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip/$num/delete");
      $this->assertNoErrorMessage();
      $this->drupalPostForm("/clouds/openstack/$cloud_context/floating_ip/$num/delete",
                            [],
                            $this->t('Delete'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Floating IP', '@label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip");
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextNotContains($edit[$j]['name']);
      }
    }
  }

  /**
   * Tests deleting Floating IPs with bulk delete operation.
   *
   * @throws \Exception
   */
  public function testFloatingIpBulkDelete(): void {

    $cloud_context = $this->cloudContext;
    $regions = ['RegionOne'];
    $this->deleteAllElasticIpInMockData();

    for ($i = 0; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++) {

      // Create Floating IPs.
      $floating_ips = $this->createElasticIpRandomTestFormData();
      $floating_ips_count = count($floating_ips);
      $network_interface_data = $this->createNetworkInterfaceTestFormData($floating_ips_count, TRUE);

      for ($j = 0; $j < $floating_ips_count; $j++) {

        $floating_ip = $floating_ips[$j];

        // Setup a test instance.
        $instance_id = 'i-' . $this->getRandomId();
        $instance = $this->createInstanceTestEntity(OpenStackInstance::class, $j, $regions, $floating_ip['PublicIp'], '', $instance_id);
        $instance_id = $this->addInstanceMockData(OpenStackInstanceTest::class, $instance->getName(), $instance->getKeyPairName(), $regions);

        // Setup a test network interface.
        $this->createNetworkInterfaceTestEntity(OpenStackNetworkInterface::class, $j, '', '', $instance_id);
        $this->addNetworkInterfaceMockData($network_interface_data[$j]);

        // Setup a test Floating IP.
        $this->createFloatingIpTestEntity($j, $floating_ip['Name'], $floating_ip['PublicIp'], $cloud_context);
        $this->addElasticIpMockData($floating_ip['Name'], $floating_ip['PublicIp'], 'standard');
      }

      $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip");

      $data = [];
      $data['action'] = 'openstack_floating_ip_delete_action';

      $checkboxes = $this->cssSelect('input[type=checkbox]');
      foreach ($checkboxes ?: [] as $checkbox) {
        if ($checkbox->getAttribute('name') === NULL) {
          continue;
        }

        $data[$checkbox->getAttribute('name')] = $checkbox->getAttribute('value');
      }

      // Confirm.
      $this->drupalPostForm(
        "/clouds/openstack/$cloud_context/floating_ip",
        $data,
        $this->t('Apply to selected items')
      );
      $this->assertNoErrorMessage();

      // Al lower case of Floating IP is correct since the original @label
      // linked
      // \Drupal\core\Entity\EntityType::getSingularLabel makes the string
      // lowercase.
      $message = 'Are you sure you want to delete these Floating IPs?';
      if ($floating_ips_count === 1) {
        $message = 'Are you sure you want to delete this Floating IP?';
      }
      $this->assertSession()->pageTextContains($message);

      foreach ($floating_ips ?: [] as $floating_ip) {
        $this->assertSession()->pageTextContains($floating_ip['Name']);
      }

      // Delete.
      $this->drupalPostForm(
        "/clouds/openstack/$cloud_context/floating_ip/delete_multiple",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();

      if ($floating_ips_count === 1) {
        $this->assertSession()->pageTextContains("Deleted $floating_ips_count Floating IP.");
      }
      else {
        $this->assertSession()->pageTextContains("Deleted $floating_ips_count Floating IPs.");
      }

      foreach ($floating_ips ?: [] as $floating_ip) {
        $t_args = ['@type' => 'Floating IP', '@label' => $floating_ip['Name']];
        $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));
        $this->deleteFirstElasticIpMockData();
      }

      // Click 'Refresh'.
      $this->clickLink($this->t('Refresh'));
      $this->assertSession()->pageTextContains($this->t('Updated Floating IPs.'));

      foreach ($floating_ips ?: [] as $floating_ip) {
        $name = $floating_ip['Name'];
        $this->assertSession()->pageTextNotContains($name);
      }
    }
  }

  /**
   * Test updating Floating IPs.
   *
   * @throws \Exception
   */
  public function testUpdateFloatingIpList(): void {

    $cloud_context = $this->cloudContext;
    $this->deleteAllElasticIpInMockData();

    // Add a new Floating IP.
    $add = $this->createElasticIpTestFormData(self::OPENSTACK_FLOATING_IP_REPEAT_COUNT);
    for ($i = 0; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++) {
      $add[$i]['public_ip'] = Utils::getRandomPublicIp();
      $this->addElasticIpMockData($add[$i]['name'], $add[$i]['public_ip'], $add[$i]['domain']);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Floating IPs.'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Make sure detailed and edit view.
    for ($i = 0, $num = 1; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++, $num++) {

      // Confirm the detailed view.
      $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip/$num");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/floating_ip/$num/edit");
      $this->assertSession()->linkExists($this->t('Associate Floating IP'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/floating_ip/$num/associate");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/floating_ip/$num/delete");
      $this->assertSession()->linkExists($this->t('List OpenStack Floating IPs'));
      // Click 'Refresh'.
      $this->clickLink($this->t('List OpenStack Floating IPs'));
      $this->assertNoErrorMessage();

      // Confirm the edit view.
      $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip/$num/edit");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/floating_ip/$num/edit");
      $this->assertSession()->linkExists($this->t('Associate Floating IP'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/floating_ip/$num/associate");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/floating_ip/$num/delete");
    }

    // Edit Floating IP information.
    for ($i = 0, $num = 1; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++, $num++) {

      // Setup a test instance.
      $instance_id = 'i-' . $this->getRandomId();

      // Change Floating IP Name in mock data.
      $add[$i]['name'] = sprintf('eip-entity #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE));
      $add[$i]['association_id'] = $this->random->name(8, TRUE);
      $this->updateElasticIpMockData($i, $add[$i]['name'], $add[$i]['association_id'], $instance_id);

    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Floating IPs'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Make sure detailed and edit view.
    for ($i = 0, $num = 1; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++, $num++) {

      // Confirm the detailed view.
      $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip/$num");
      $this->assertSession()->linkExists($this->t('Disassociate'));

      // Confirm the edit view.
      $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip/$num/edit");
      $this->assertSession()->linkExists($this->t('Disassociate Floating IP'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/floating_ip/$num/disassociate");
    }

    // Update Floating IP tags.
    for ($i = 0, $num = 1; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++, $num++) {

      // Update tags.
      $add[$i]['tags_name'] = $this->getRandomId();
      $this->updateTagsInMockData($num - 1, 'Addresses', 'Name', $add[$i]['tags_name'], FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip");
    for ($i = 0; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['tags_name']);
    }

    // Update Floating IP tags for empty.
    for ($i = 0; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'Addresses', 'Name', '', FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip");
    for ($i = 0; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['tags_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['public_ip']);
    }

    // Delete Floating IP tags.
    for ($i = 0; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'Addresses', 'Name', '', TRUE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip");
    for ($i = 0; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['public_ip']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['public_ip']);
    }

    // Delete Floating IP in mock data.
    for ($i = 0; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++) {
      $this->deleteFirstElasticIpMockData();
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/floating_ip");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Floating IPs.'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_FLOATING_IP_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

}
