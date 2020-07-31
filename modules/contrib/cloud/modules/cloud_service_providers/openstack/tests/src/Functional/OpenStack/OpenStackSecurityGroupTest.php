<?php

namespace Drupal\Tests\openstack\Functional\OpenStack;

use Drupal\openstack\Entity\OpenStackSecurityGroup;
use Drupal\Tests\openstack\Functional\OpenStackTestBase;
use Drupal\Tests\cloud\Functional\Utils;

/**
 * Tests OpenStack Security Group.
 *
 * @group OpenStack
 */
class OpenStackSecurityGroupTest extends OpenStackTestBase {

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list openstack security group',
      'add openstack security group',
      'view any openstack security group',
      'edit any openstack security group',
      'delete any openstack security group',
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  protected function getMockDataTemplateVars(): array {
    return [
      'vpc_id' => 'vpc-' . $this->getRandomId(),
      'cidr_block' => Utils::getRandomCidr(),
      'group_id' => 'sg-' . $this->getRandomId(),
      'group_name' => $this->random->name(8, TRUE),
    ];
  }

  /**
   * Tests CRUD for Security Group information.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Exception
   */
  public function testSecurityGroup(): void {
    $cloud_context = $this->cloudContext;

    $this->deleteVpcMockData(0);

    // List Security Group for OpenStack.
    $this->drupalGet("/clouds/openstack/$cloud_context/security_group");
    $this->assertNoErrorMessage();

    $this->drupalGet("/clouds/openstack/$cloud_context/security_group/add");
    $this->assertSession()->pageTextContains($this->t('You do not have any VPCs. You need a VPC in order to create a security group. You can create a VPC.'));

    // Add a new Security Group.
    $add = $this->createSecurityGroupTestFormData(self::$openStackSecurityGroupRepeatCount);
    $addVpc = $this->createVpcTestFormData(self::$openStackSecurityGroupRepeatCount);

    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount; $i++) {
      $this->reloadMockData();

      $this->drupalPostForm("/clouds/openstack/$cloud_context/security_group/add",
          $add[$i],
          $this->t('Save'));

      $this->assertSession()->pageTextContains($this->t('VPC CIDR (ID) field is required.'));

      $defaults = $this->latestTemplateVars;
      $add[$i]['vpc_id'] = $defaults['vpc_id'];

      // Create VPC.
      $this->addVpcMockData($addVpc[$i], $add[$i]['vpc_id']);

      $this->drupalPostForm("/clouds/openstack/$cloud_context/security_group/add",
                            $add[$i],
                            $this->t('Save'));

      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['group_name[0][value]']);
      $t_args = ['@type' => 'Security Group', '%label' => $add[$i]['group_name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/openstack/$cloud_context/security_group");
      $this->assertNoErrorMessage();
      // 3 times.
      for ($j = 0; $j < $i + 1; $j++) {
        $this->assertSession()->pageTextContains($add[$i]['group_name[0][value]']);
      }
    }

    for ($i = 0, $num = 1; $i < self::$openStackSecurityGroupRepeatCount; $i++, $num++) {
      // Make sure the all security_group listing exists.
      $this->drupalGet('/clouds/openstack/security_group');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['group_name[0][value]']);
      }
    }

    // Security Group doesn't have an edit operation.
    // Edit an Security Group information.
    $edit = $this->createSecurityGroupTestFormData(self::$openStackSecurityGroupRepeatCount, TRUE);
    for ($i = 0, $num = 1; $i < self::$openStackSecurityGroupRepeatCount; $i++, $num++) {

      unset($edit[$i]['description']);

      // Initialize the mock data. Run security_group update so the data
      // gets imported.
      $this->reloadMockData();

      $this->drupalPostForm("/clouds/openstack/$cloud_context/security_group/$num/edit",
                            $edit[$i],
                            $this->t('Save'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Security Group', '%label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/openstack/$cloud_context/security_group");
      $this->assertNoErrorMessage();

      $this->assertSession()->pageTextContains($edit[$i]['name']);
    }

    // Delete Security Group.
    for ($i = 0, $num = 1; $i < self::$openStackSecurityGroupRepeatCount; $i++, $num++) {

      $this->drupalGet("/clouds/openstack/$cloud_context/security_group/$num/delete");
      $this->assertNoErrorMessage();
      $this->drupalPostForm("/clouds/openstack/$cloud_context/security_group/$num/delete",
                            [],
                            $this->t('Delete'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Security Group', '@label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/openstack/$cloud_context/security_group");
      $this->assertNoErrorMessage();
    }
  }

  /**
   * Tests deleting security groups with bulk operation.
   *
   * @throws \Exception
   */
  public function testSecurityGroupBulk(): void {
    $cloud_context = $this->cloudContext;

    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount; $i++) {
      // Create security groups.
      $security_groups = $this->createSecurityGroupRandomTestFormData();
      $index = 0;
      $entities = [];
      foreach ($security_groups ?: [] as $security_group) {
        $entities[] = $this->createSecurityGroupTestEntity(OpenStackSecurityGroup::class, $index++, $security_group['GroupId'], $security_group['Name'], '', $cloud_context);
      }

      $this->runTestEntityBulk('security_group', $entities);
    }
  }

  /**
   * Tests updating security groups.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testUpdateSecurityGroupList(): void {
    $cloud_context = $this->cloudContext;

    // Delete init mock data.
    $this->deleteFirstSecurityGroupMockData();

    // Add new Security Groups.
    $add = $this->createSecurityGroupTestFormData(self::$openStackSecurityGroupRepeatCount);
    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount; $i++) {
      $this->addSecurityGroupMockData($add[$i]['group_name[0][value]'], $add[$i]['description']);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/security_group");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['group_name[0][value]']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Security Groups.'));
    // Make sure listing.
    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['group_name[0][value]']);
    }

    // Make sure detailed and edit view.
    $num = 1;
    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount; $i++, $num++) {

      // Confirm the detailed view.
      $this->drupalGet("/clouds/openstack/$cloud_context/security_group/$num");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/security_group/$num/edit");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/security_group/$num/delete");
      $this->assertSession()->linkExists($this->t('List OpenStack Security Groups'));
      // Click 'Refresh'.
      $this->clickLink($this->t('List OpenStack Security Groups'));
      $this->assertNoErrorMessage();

      // Confirm the edit view.
      $this->drupalGet("/clouds/openstack/$cloud_context/security_group/$num/edit");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/security_group/$num/edit");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/security_group/$num/delete");
      $this->assertSession()->linkExists('Edit');
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/security_group/$num/edit");
    }

    // Add a new Security Group.
    $num++;
    $data = [
      'description' => "Description #$num - {$this->random->name(64, TRUE)}",
      'group_name[0][value]' => "group-name-#$num - {$this->random->name(15, TRUE)}",
    ];
    $this->addSecurityGroupMockData($data['group_name[0][value]'], $data['description']);

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/security_group");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextNotContains($data['group_name[0][value]']);

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Security Groups.'));
    $add = array_merge($add, [$data]);
    // Make sure listing.
    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount + 1; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['group_name[0][value]']);
    }

    // Update tags.
    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount; $i++) {
      $add[$i]['tags_name'] = $this->getRandomId();
      $this->updateTagsInMockData($i, 'SecurityGroups', 'Name', $add[$i]['tags_name'], FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/security_group");
    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount; $i++) {
      $this->assertSession()->linkExists($add[$i]['tags_name']);
    }

    // Update tags for empty.
    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'SecurityGroups', 'Name', '', FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/security_group");
    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount; $i++) {
      $this->assertSession()->linkExists($add[$i]['tags_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));

    // Make sure listing.
    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['group_name[0][value]']);
    }

    // Delete name tags.
    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'SecurityGroups', 'Name', '', TRUE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/security_group");
    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['group_name[0][value]']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['group_name[0][value]']);
    }

    // Delete SecurityGroup in mock data.
    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount + 1; $i++) {
      $this->deleteFirstSecurityGroupMockData();
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/security_group");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount + 1; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['group_name[0][value]']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Security Groups.'));
    // Make sure listing.
    for ($i = 0; $i < self::$openStackSecurityGroupRepeatCount + 1; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['group_name[0][value]']);
    }
  }

  /**
   * Create rule parameters.
   *
   * @param array $rules
   *   The array of rules.
   * @param string $source_group_id
   *   The group ID of copied security group.
   *
   * @return array
   *   The params created.
   *
   * @throws \Exception
   */
  private function createRuleParams(array $rules, $source_group_id): array {
    $params = [];
    $inbound_index = 0;
    $outbound_index = 0;

    foreach ($rules ?: [] as $rule) {
      $perm = $this->getRulePermission($rule['source'], $rule['type'], $source_group_id);

      unset(
        $perm['source'],
        $perm['type']
      );

      $keys = array_rand($perm, random_int(1, count($perm) - 1));
      if (is_array($keys)) {
        foreach ($keys ?: [] as $key) {
          $rule[$key] = $perm[$key];
        }
      }
      else {
        $rule[$keys] = $perm[$keys];
      }
    }

    foreach ($rules ?: [] as $rule) {
      if ($rule['type'] === self::$openStackSecurityGroupRulesInbound) {
        $index = $inbound_index++;
        $prefix = 'ip_permission';
      }
      else {
        $index = $outbound_index++;
        $prefix = 'outbound_permission';
      }
      foreach ($rule ?: [] as $key => $value) {
        if ($key === 'type') {
          continue;
        }
        $params["${prefix}[${index}][${key}]"] = $value;
      }
    }

    return $params;
  }

  /**
   * Get random rule permission.
   *
   * @param string $source
   *   The permission source.
   * @param int $type
   *   The permission type.
   * @param string $group_id
   *   The group ID.
   *
   * @return array
   *   The array of rule permission.
   *
   * @throws \Exception
   */
  private function getRulePermission($source = NULL, $type = 0, $group_id = NULL): array {
    $type = $type ?: self::$openStackSecurityGroupRulesInbound;
    if (!isset($source)) {
      $idx = random_int(0, 2);
      $source = ['ip4', 'ip6', 'group'][$idx];
    }
    if (!isset($group_id)) {
      $group_id = 'sg-' . $this->getRandomId();
    }
    $permissions = [
      'ip4' => [
        'type' => $type,
        'source' => 'ip4',
        'cidr_ip' => Utils::getRandomCidr(),
        'from_port' => Utils::getRandomFromPort(),
        'to_port' => Utils::getRandomToPort(),
      ],
      'ip6' => [
        'type' => $type,
        'source' => 'ip6',
        'cidr_ip_v6' => Utils::getRandomCidrV6(),
        'from_port' => Utils::getRandomFromPort(),
        'to_port' => Utils::getRandomToPort(),
      ],
      'group' => [
        'type' => $type,
        'source' => 'group',
        'user_id' => $this->random->name(8, TRUE),
        'group_id' => $group_id,
        'vpc_id' => 'vpc-' . $this->getRandomId(),
        'peering_connection_id' => 'pcx-' . $this->getRandomId(),
        'peering_status' => 'active',
        'from_port' => Utils::getRandomFromPort(),
        'to_port' => Utils::getRandomToPort(),
      ],
    ];
    return $permissions[$source];
  }

}
