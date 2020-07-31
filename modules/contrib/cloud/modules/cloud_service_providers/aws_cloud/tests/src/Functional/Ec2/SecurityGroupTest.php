<?php

namespace Drupal\Tests\aws_cloud\Functional\Ec2;

use Drupal\aws_cloud\Entity\Ec2\SecurityGroup;
use Drupal\Tests\aws_cloud\Functional\AwsCloudTestBase;
use Drupal\Tests\cloud\Functional\Utils;

/**
 * Tests AWS Cloud Security Group.
 *
 * @group AWS Cloud
 */
class SecurityGroupTest extends AwsCloudTestBase {

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list aws cloud security group',
      'add aws cloud security group',
      'view any aws cloud security group',
      'edit any aws cloud security group',
      'delete any aws cloud security group',
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

    // List Security Group for Amazon EC2.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/security_group");
    $this->assertNoErrorMessage();

    $this->drupalGet("/clouds/aws_cloud/$cloud_context/security_group/add");
    $this->assertSession()->pageTextContains($this->t('You do not have any VPCs. You need a VPC in order to create a security group. You can create a VPC.'));

    // Add a new Security Group.
    $add = $this->createSecurityGroupTestFormData(self::$awsCloudSecurityGroupRepeatCount);
    $addVpc = $this->createVpcTestFormData(self::$awsCloudSecurityGroupRepeatCount);

    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount; $i++) {
      $this->reloadMockData();

      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/security_group/add",
          $add[$i],
          $this->t('Save'));

      $this->assertSession()->pageTextContains($this->t('VPC CIDR (ID) field is required.'));

      $defaults = $this->latestTemplateVars;
      $add[$i]['vpc_id'] = $defaults['vpc_id'];

      // Create VPC.
      $this->addVpcMockData($addVpc[$i], $add[$i]['vpc_id']);

      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/security_group/add",
                            $add[$i],
                            $this->t('Save'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Security Group', '%label' => $add[$i]['group_name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/security_group");
      $this->assertNoErrorMessage();
      // 3 times.
      for ($j = 0; $j < $i + 1; $j++) {
        $this->assertSession()->pageTextContains($add[$i]['group_name[0][value]']);
      }
    }

    for ($i = 0, $num = 1; $i < self::$awsCloudSecurityGroupRepeatCount; $i++, $num++) {
      // Make sure the all security_group listing exists.
      $this->drupalGet('/clouds/aws_cloud/security_group');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['group_name[0][value]']);
      }
    }

    // Security Group doesn't have an edit operation.
    // Edit an Security Group information.
    $edit = $this->createSecurityGroupTestFormData(self::$awsCloudSecurityGroupRepeatCount, TRUE);
    for ($i = 0, $num = 1; $i < self::$awsCloudSecurityGroupRepeatCount; $i++, $num++) {

      unset($edit[$i]['description']);

      // Initialize the mock data. Run security_group update so the data
      // gets imported.
      $this->reloadMockData();

      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/security_group/$num/edit",
                            $edit[$i],
                            $this->t('Save'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Security Group', '%label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/security_group");
      $this->assertNoErrorMessage();

      $this->assertSession()->pageTextContains($edit[$i]['name']);
    }

    // Delete Security Group.
    for ($i = 0, $num = 1; $i < self::$awsCloudSecurityGroupRepeatCount; $i++, $num++) {
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/security_group/$num/delete");
      $this->assertNoErrorMessage();
      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/security_group/$num/delete",
                            [],
                            $this->t('Delete'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Security Group', '@label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/security_group");
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

    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount; $i++) {
      // Create security groups.
      $security_groups = $this->createSecurityGroupRandomTestFormData();
      $index = 0;
      $entities = [];
      foreach ($security_groups ?: [] as $security_group) {
        $entities[] = $this->createSecurityGroupTestEntity(SecurityGroup::class, $index++, $security_group['GroupId'], $security_group['Name'], '', $cloud_context);
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
    $add = $this->createSecurityGroupTestFormData(self::$awsCloudSecurityGroupRepeatCount);
    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount; $i++) {
      $this->addSecurityGroupMockData($add[$i]['group_name[0][value]'], $add[$i]['description']);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/security_group");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['group_name[0][value]']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Security Groups.'));
    // Make sure listing.
    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['group_name[0][value]']);
    }

    // Make sure detailed and edit view.
    $num = 1;
    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount; $i++, $num++) {

      // Confirm the detailed view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/security_group/$num");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/security_group/$num/edit");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/security_group/$num/delete");
      $this->assertSession()->linkExists($this->t('List AWS Cloud Security Groups'));
      // Click 'Refresh'.
      $this->clickLink($this->t('List AWS Cloud Security Groups'));
      $this->assertNoErrorMessage();

      // Confirm the edit view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/security_group/$num/edit");
      $this->assertSession()->linkNotExists($this->t('Edit'));
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/security_group/$num/delete");
      $this->assertSession()->linkNotExists('Edit');
    }

    // Add a new Security Group.
    $num++;
    $data = [
      'description' => "Description #$num - {$this->random->name(64, TRUE)}",
      'group_name[0][value]' => "group-name-#$num - {$this->random->name(15, TRUE)}",
    ];
    $this->addSecurityGroupMockData($data['group_name[0][value]'], $data['description']);

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/security_group");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextNotContains($data['group_name[0][value]']);

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Security Groups.'));
    $add = array_merge($add, [$data]);
    // Make sure listing.
    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount + 1; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['group_name[0][value]']);
    }

    // Update tags.
    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount; $i++) {
      $add[$i]['tags_name'] = $this->getRandomId();
      $this->updateTagsInMockData($i, 'SecurityGroups', 'Name', $add[$i]['tags_name'], FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/security_group");
    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount; $i++) {
      $this->assertSession()->linkExists($add[$i]['tags_name']);
    }

    // Update tags for empty.
    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'SecurityGroups', 'Name', '', FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/security_group");
    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount; $i++) {
      $this->assertSession()->linkExists($add[$i]['tags_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));

    // Make sure listing.
    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['group_name[0][value]']);
    }

    // Delete name tags.
    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'SecurityGroups', 'Name', '', TRUE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/security_group");
    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['group_name[0][value]']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['group_name[0][value]']);
    }

    // Delete SecurityGroup in mock data.
    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount + 1; $i++) {
      $this->deleteFirstSecurityGroupMockData();
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/security_group");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount + 1; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['group_name[0][value]']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Security Groups.'));
    // Make sure listing.
    for ($i = 0; $i < self::$awsCloudSecurityGroupRepeatCount + 1; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['group_name[0][value]']);
    }

  }

  /**
   * Test for copying security groups.
   */
  public function testSecurityGroupCopy() {
    try {
      $this->repeatTestSecurityGroupCopy(self::$awsCloudSecurityGroupRepeatCount);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

  /**
   * Test for copying security groups.
   *
   * @param int $max_test_repeat_count
   *   Max test repeating count.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function repeatTestSecurityGroupCopy($max_test_repeat_count = 1): void {
    $cloud_context = $this->cloudContext;

    // Delete init mock data.
    $this->deleteFirstSecurityGroupMockData();

    $add = $this->createSecurityGroupTestFormData($max_test_repeat_count);
    $addVpc = $this->createVpcTestFormData($max_test_repeat_count);
    $copy = $this->createSecurityGroupTestFormData($max_test_repeat_count);

    for ($i = 0, $num = 1; $i < $max_test_repeat_count; $i++, $num += 2) {

      $defaults = $this->getMockDataTemplateVars();
      $add[$i]['vpc_id'] = $defaults['vpc_id'];

      // Create VPC.
      $this->addVpcMockData($addVpc[$i], $add[$i]['vpc_id']);
      $vpc = $this->createVpcTestEntity($i, $add[$i]['vpc_id'], $addVpc[$i]['name'], $cloud_context);
      $vpc->setCidrBlock($addVpc[$i]['cidr_block']);
      $vpc->save();

      // Add mock data.
      $add[$i]['group_id'] = $this->addSecurityGroupMockData($add[$i]['group_name[0][value]'], $add[$i]['description'], $add[$i]['vpc_id']);
      // Create entity.
      $security_group = $this->createSecurityGroupTestEntity(SecurityGroup::class, $i, $add[$i]['group_id'], $add[$i]['group_name[0][value]'], $add[$i]['vpc_id'], $cloud_context);
      $security_group->set('description', $add[$i]['description']);
      $security_group->save();

      $edit_url = "/clouds/aws_cloud/$cloud_context/security_group/$num/edit";

      // Create rules.
      $add[$i]['rules'] = $this->createRulesTestFormData(self::$awsCloudSecurityGroupRulesMix, $edit_url, $add[$i]['group_id'], self::$awsCloudSecurityGroupRulesRepeatCount);

      $this->drupalGet("/clouds/aws_cloud/$cloud_context/security_group/$num");
      $this->assertSession()->linkExists($this->t('Copy'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/security_group/$num/copy");

      // Click 'Copy'.
      $this->clickLink($this->t('Copy'));
      $this->assertSession()->pageTextContains('Copy AWS Cloud Security Group');
      $this->assertSession()->fieldValueEquals('group_name[0][value]', "Copy of {$add[$i]['group_name[0][value]']}");

      $copy[$i]['vpc_id'] = $add[$i]['vpc_id'];

      $params = $this->createRuleParams($add[$i]['rules'], $add[$i]['group_id']);

      $copy[$i] = array_merge($copy[$i], $params);

      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/security_group/$num/copy",
        $copy[$i],
        $this->t('Copy'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Security Group', '%label' => $copy[$i]['group_name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      foreach ($params ?: [] as $key => $value) {
        if (strpos($key, 'source') === FALSE) {
          $this->assertSession()->pageTextContains($value);
        }
      }

      $this->drupalGet("/clouds/aws_cloud/$cloud_context/security_group");
      $this->assertSession()->pageTextContains($copy[$i]['group_name[0][value]']);
    }

  }

  /**
   * Test for copying security groups without vpc.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testSecurityGroupCopyNoVpc(): void {
    $cloud_context = $this->cloudContext;

    // Delete init mock data.
    $this->deleteFirstSecurityGroupMockData();
    $this->deleteVpcMockData(0);

    $add = $this->createSecurityGroupTestFormData(self::$awsCloudSecurityGroupRepeatCount);

    for ($i = 0, $num = 1; $i < self::$awsCloudSecurityGroupRepeatCount; $i++, $num++) {

      // Add mock data.
      $add[$i]['group_id'] = $this->addSecurityGroupMockData($add[$i]['group_name[0][value]'], $add[$i]['description']);
      // Create entity.
      $security_group = $this->createSecurityGroupTestEntity(SecurityGroup::class, $i, $add[$i]['group_id'], $add[$i]['group_name[0][value]'], '', $cloud_context);
      $security_group->set('description', $add[$i]['description']);
      $security_group->save();

      $this->drupalGet("/clouds/aws_cloud/$cloud_context/security_group/$num/copy");
      $this->assertSession()->pageTextContains($this->t('You do not have any VPCs. You need a VPC in order to create a security group. You can create a VPC.'));
    }
  }

  /**
   * Test duplicate security group names and 'default' name.
   */
  public function testSecurityGroupName() : void {
    $cloud_context = $this->cloudContext;
    $add = $this->createSecurityGroupTestFormData(self::$awsCloudSecurityGroupRepeatCount);
    $addVpc = $this->createVpcTestFormData(self::$awsCloudSecurityGroupRepeatCount);

    for ($i = 0, $num = 1; $i < self::$awsCloudSecurityGroupRepeatCount; $i++, $num++) {
      // Create entity.
      $this->reloadMockData();
      $defaults = $this->latestTemplateVars;
      $add[$i]['vpc_id'] = $defaults['vpc_id'];

      $this->addVpcMockData($addVpc[$i], $add[$i]['vpc_id']);
      $this->addSecurityGroupMockData($add[$i]['group_name[0][value]'], $add[$i]['description'], $add[$i]['vpc_id']);

      // Switch to 'default'.
      $real_group_name = $add[$i]['group_name[0][value]'];
      $add[$i]['group_name[0][value]'] = 'default';
      // Try to save it a second time through the form.
      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/security_group/add",
        $add[$i],
        $this->t('Save'));
      $this->assertSession()->pageTextContains($this->t('Cannot create group with Security Group Name "default".'));

      // Save it for real.
      $add[$i]['group_name[0][value]'] = $real_group_name;
      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/security_group/add",
        $add[$i],
        $this->t('Save'));
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Security Group', '%label' => $add[$i]['group_name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Try to save it a second time through the form.
      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/security_group/add",
        $add[$i],
        $this->t('Save'));

      $this->assertSession()->pageTextContains($this->t('The Security Group Name "@name" already exists.', [
        '@name' => $add[$i]['group_name[0][value]'],
      ]));
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
      if ($rule['type'] === self::$awsCloudSecurityGroupRulesInbound) {
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
    $type = $type ?: self::$awsCloudSecurityGroupRulesInbound;
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
