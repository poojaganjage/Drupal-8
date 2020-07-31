<?php

namespace Drupal\Tests\openstack\Functional\OpenStack;

use Drupal\Tests\openstack\Functional\OpenStackTestBase;
use Drupal\Tests\cloud\Functional\Utils;

/**
 * Tests OpenStack Security Group by focusing on IpPermissions only.
 *
 * @group OpenStack
 */
class OpenStackSecurityGroupIpPermissionsTest extends OpenStackTestBase {

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
   * Test that permissions are being pulled in from the API.
   */
  public function testIpPermissionsUpdateFromApi(): void {
    try {
      $this->repeatTestIpPermissionsUpdateFromApi(self::$openStackSecurityGroupRepeatCount);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

  /**
   * Private test function.
   *
   * @param int $max_test_repeat_count
   *   Max test repeating count.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  private function repeatTestIpPermissionsUpdateFromApi($max_test_repeat_count = 1): void {
    $cloud_context = $this->cloudContext;
    for ($i = 0; $i < $max_test_repeat_count; $i++) {
      $this->reloadMockData();

      // Get the default variables.
      $defaults = $this->latestTemplateVars;

      $rules = [
        [
          'type' => self::$openStackSecurityGroupRulesInbound,
          'source' => 'ip4',
          'cidr_ip' => Utils::getRandomCidr(),
          'from_port' => Utils::getRandomFromPort(),
          'to_port' => Utils::getRandomToPort(),
        ],
        [
          'type' => self::$openStackSecurityGroupRulesInbound,
          'source' => 'ip6',
          'cidr_ip_v6' => Utils::getRandomCidrV6(),
          'from_port' => Utils::getRandomFromPort(),
          'to_port' => Utils::getRandomToPort(),
        ],
        [
          'type' => self::$openStackSecurityGroupRulesInbound,
          'source' => 'group',
          'user_id' => $this->random->name(8, TRUE),
          'group_id' => 'sg-' . $this->getRandomId(),
          'vpc_id' => 'vpc-' . $this->getRandomId(),
          'peering_connection_id' => 'pcx-' . $this->getRandomId(),
          'peering_status' => 'active',
          'from_port' => Utils::getRandomFromPort(),
          'to_port' => Utils::getRandomToPort(),
        ],
        [
          'type' => self::$openStackSecurityGroupRulesOutbound,
          'source' => 'ip4',
          'cidr_ip' => Utils::getRandomCidr(),
          'from_port' => Utils::getRandomFromPort(),
          'to_port' => Utils::getRandomToPort(),
        ],
        [
          'type' => self::$openStackSecurityGroupRulesOutbound,
          'source' => 'ip6',
          'cidr_ip_v6' => Utils::getRandomCidrV6(),
          'from_port' => Utils::getRandomFromPort(),
          'to_port' => Utils::getRandomToPort(),
        ],
        [
          'type' => self::$openStackSecurityGroupRulesOutbound,
          'source' => 'group',
          'user_id' => $this->random->name(8, TRUE),
          'group_id' => 'sg-' . $this->getRandomId(),
          'vpc_id' => 'vpc-' . $this->getRandomId(),
          'peering_connection_id' => 'pcx-' . $this->getRandomId(),
          'peering_status' => 'active',
          'from_port' => Utils::getRandomFromPort(),
          'to_port' => Utils::getRandomToPort(),
        ],
      ];

      $this->updateRulesMockData($rules, self::$openStackSecurityGroupRulesOutbound);

      $this->drupalGet("/clouds/openstack/$cloud_context/security_group/update");
      $this->assertNoErrorMessage();

      // Navigate to the group listing page.
      $this->drupalGet("/clouds/openstack/$cloud_context/security_group");

      // Click on a specific group.
      $this->clickLink($defaults['group_name']);
      $this->assertSession()->pageTextContains($defaults['group_name']);

      // Assert permissions.
      foreach ($rules ?: [] as $rule) {
        foreach ($rule ?: [] as $key => $value) {
          if ($key === 'type' || $key === 'source') {
            continue;
          }

          $this->assertSession()->pageTextContains(
            $rule[$key]
          );
        }
      }
    }
  }

  /**
   * Test for editing IP permissions.
   */
  public function testIpPermissionsEdit(): void {
    try {
      $this->repeatTestIpPermissionsEdit(self::$openStackSecurityGroupRepeatCount);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

  /**
   * Test for editing IP permissions.
   *
   * @param int $max_test_repeat_count
   *   Max test repeating count.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  private function repeatTestIpPermissionsEdit($max_test_repeat_count = 1): void {
    $cloud_context = $this->cloudContext;

    $add = $this->createSecurityGroupTestFormData(self::$openStackSecurityGroupRepeatCount);

    for ($i = 0, $num = 1; $i < $max_test_repeat_count; $i++, $num++) {

      $this->reloadMockData();

      $defaults = $this->latestTemplateVars;
      $defaults['group_name'] = $add[$i]['group_name[0][value]'];
      $add[$i]['vpc_id'] = $defaults['vpc_id'];

      $this->drupalGet("/clouds/openstack/$cloud_context/security_group/add");
      $this->drupalPostForm("/clouds/openstack/$cloud_context/security_group/add",
        $add[$i],
        $this->t('Save'));

      // After save, assert the save is successful.
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Security Group', '%label' => $add[$i]['group_name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      $edit_url = "/clouds/openstack/$cloud_context/security_group/$num/edit";
      $view_url = "/clouds/openstack/$cloud_context/security_group/$num";

      // Test case 1. (Inbound rule add (only) / delete).
      $rules = $this->createRulesTestFormData(self::$openStackSecurityGroupRulesInbound, $edit_url, 1, self::$openStackSecurityGroupRulesRepeatCount);

      $this->revokeRulesTestFormData($rules, $view_url);

      // Test case 2. (Outbound rule (only) add / delete).
      $rules = $this->createRulesTestFormData(self::$openStackSecurityGroupRulesOutbound, $edit_url, 1, self::$openStackSecurityGroupRulesRepeatCount);
      $this->revokeRulesTestFormData($rules, $view_url);

      // Test case 3. (Combination of mixing above Test case 1. and 2.).
      $rules = $this->createRulesTestFormData(self::$openStackSecurityGroupRulesMix, $edit_url, 1, self::$openStackSecurityGroupRulesRepeatCount);

      // Test case3.2 edit rules.
      $params = $this->editRuleParams($rules, FALSE);
      $params['name'] = $add[$i]['group_name[0][value]'];
      $this->drupalPostForm($edit_url,
          $params,
          $this->t('Save'));

      $t_args = ['@type' => 'Security Group', '%label' => $params['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      // Confirm the values of edit form.
      $this->confirmRulesFormData($rules, $edit_url);

      $this->revokeRulesTestFormData($rules, $view_url);

    }
  }

  /**
   * Test the validation constraints.
   */
  public function testIpPermissionsValidate(): void {
    try {
      $this->repeatTestIpPermissionsValidate(self::$openStackSecurityGroupRepeatCount);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

  /**
   * Test the validation constraints.
   *
   * @param int $max_test_repeat_count
   *   Max test repeating count.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  private function repeatTestIpPermissionsValidate($max_test_repeat_count = 1): void {
    $cloud_context = $this->cloudContext;

    $add = $this->createSecurityGroupTestFormData(self::$openStackSecurityGroupRepeatCount);

    for ($i = 0; $i < $max_test_repeat_count; $i++) {

      $this->reloadMockData();

      $defaults = $this->latestTemplateVars;
      $defaults['group_name'] = $add[$i]['group_name[0][value]'];
      $add[$i]['vpc_id'] = $defaults['vpc_id'];

      $this->drupalGet("/clouds/openstack/$cloud_context/security_group/add");
      $this->drupalPostForm("/clouds/openstack/$cloud_context/security_group/add",
        $add[$i],
        $this->t('Save'));

      // After save, assert the save is successful.
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Security Group', '%label' => $add[$i]['group_name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Verify From port validation error.
      $rules = [
        'ip_permission[0][from_port]' => $this->random->name(2, TRUE),
        'ip_permission[0][to_port]' => Utils::getRandomToPort(),
        'ip_permission[0][cidr_ip]' => Utils::getRandomCidr(),
        'ip_permission[0][source]' => 'ip4',
      ];
      $this->drupalPostForm($this->getUrl(), $rules, $this->t('Save'));
      $this->assertSession()->pageTextContains($this->t('The From Port is not numeric.'));

      // Verify From port validation error.
      $rules = [
        'ip_permission[0][from_port]' => Utils::getRandomFromPort(),
        'ip_permission[0][to_port]' => $this->random->name(2, TRUE),
        'ip_permission[0][cidr_ip]' => Utils::getRandomCidr(),
        'ip_permission[0][source]' => 'ip4',
      ];
      $this->drupalPostForm($this->getUrl(), $rules, $this->t('Save'));
      $this->assertSession()->pageTextContains($this->t('The To Port is not numeric.'));

      // Verify CIDR IP empty test.
      $rules = [
        'ip_permission[0][from_port]' => Utils::getRandomFromPort(),
        'ip_permission[0][to_port]' => Utils::getRandomToPort(),
        'ip_permission[0][cidr_ip]' => '',
        'ip_permission[0][source]' => 'ip4',
      ];
      $this->drupalPostForm($this->getUrl(), $rules, $this->t('Save'));
      $this->assertSession()->pageTextContains($this->t('CIDR IP is empty.'));

      // Verify valid CIDR IP address.
      $rules = [
        'ip_permission[0][from_port]' => Utils::getRandomFromPort(),
        'ip_permission[0][to_port]' => Utils::getRandomToPort(),
        'ip_permission[0][cidr_ip]' => Utils::getRandomPublicIp(),
        'ip_permission[0][source]' => 'ip4',
      ];
      $this->drupalPostForm($this->getUrl(), $rules, $this->t('Save'));
      $this->assertSession()->pageTextContains($this->t('CIDR IP is not valid. Single IP addresses must be in x.x.x.x/32 notation.'));

      // Verify valid CIDR IPv6 address.
      $rules = [
        'ip_permission[0][from_port]' => Utils::getRandomFromPort(),
        'ip_permission[0][to_port]' => Utils::getRandomToPort(),
        'ip_permission[0][cidr_ip_v6]' => Utils::getRandomPublicIp(),
        'ip_permission[0][source]' => 'ip6',
      ];
      $this->drupalPostForm($this->getUrl(), $rules, $this->t('Save'));
      $this->assertSession()->pageTextContains($this->t('CIDR IPv6 is not valid. Single IP addresses must be in x.x.x.x/32 notation.'));

      // Verify CIDR IPv6 empty test.
      $rules = [
        'ip_permission[0][from_port]' => Utils::getRandomFromPort(),
        'ip_permission[0][to_port]' => Utils::getRandomToPort(),
        'ip_permission[0][cidr_ip]' => '',
        'ip_permission[0][source]' => 'ip6',
      ];
      $this->drupalPostForm($this->getUrl(), $rules, $this->t('Save'));
      $this->assertSession()->pageTextContains($this->t('CIDR IPv6 is empty.'));

      // Verify Group ID.
      $rules = [
        'ip_permission[0][from_port]' => Utils::getRandomFromPort(),
        'ip_permission[0][to_port]' => Utils::getRandomToPort(),
        'ip_permission[0][group_id]' => '',
        'ip_permission[0][source]' => 'group',
      ];
      $this->drupalPostForm($this->getUrl(), $rules, $this->t('Save'));
      $this->assertSession()->pageTextContains($this->t('Group ID is empty.'));

      // Verify to port is not greater than from port.
      $rules = [
        'ip_permission[0][from_port]' => Utils::getRandomToPort(),
        'ip_permission[0][to_port]' => Utils::getRandomFromPort(),
        'ip_permission[0][cidr_ip]' => Utils::getRandomCidr(),
        'ip_permission[0][source]' => 'ip4',
      ];
      $this->drupalPostForm($this->getUrl(), $rules, $this->t('Save'));
      $this->assertSession()->pageTextContains($this->t('From Port is greater than To Port.'));
    }

  }

  /**
   * Test for update IP permissions.
   */
  public function testUpdateIpPermissions(): void {
    try {
      $this->repeatTestUpdateIpPermissions(self::$openStackSecurityGroupRepeatCount);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

  /**
   * Test for updating IP permissions.
   *
   * @param int $max_test_repeat_count
   *   Max test repeating count.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Exception
   */
  private function repeatTestUpdateIpPermissions($max_test_repeat_count = 1): void {
    $cloud_context = $this->cloudContext;

    $add = $this->createSecurityGroupTestFormData(self::$openStackSecurityGroupRepeatCount);

    for ($i = 0, $num = 1; $i < $max_test_repeat_count; $i++, $num++) {

      $this->reloadMockData();

      $defaults = $this->latestTemplateVars;
      $defaults['group_name'] = $add[$i]['group_name[0][value]'];
      $add[$i]['vpc_id'] = $defaults['vpc_id'];

      $this->drupalGet("/clouds/openstack/$cloud_context/security_group/add");
      $this->drupalPostForm("/clouds/openstack/$cloud_context/security_group/add",
          $add[$i],
          $this->t('Save'));

      // After save, assert the save is successful.
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Security Group', '%label' => $add[$i]['group_name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      $edit_url = "/clouds/openstack/$cloud_context/security_group/$num/edit";

      // Add rules.
      $add_rules = $this->createRulesTestFormData(self::$openStackSecurityGroupRulesMix, $edit_url, 1, self::$openStackSecurityGroupRulesRepeatCount);

      // Create rules for mock data.
      $count = count($add_rules) > 1 ? random_int(1, count($add_rules) - 1) : 1;
      $types = [self::$openStackSecurityGroupRulesInbound, self::$openStackSecurityGroupRulesOutbound];
      $rules = [];
      $idx = 0;
      while ($idx < $count) {
        $type = $types[array_rand($types)];
        $rule = ['type' => $type];
        $this->getRandomRule($rule);
        $rules[] = $rule;
        $idx++;
      }
      // Update rules in mock data.
      $this->updateRulesMockData($rules, self::$openStackSecurityGroupRulesOutbound);

      // Update.
      $this->drupalGet("/clouds/openstack/$cloud_context/security_group/update");

      // Confirm the values of edit form.
      $this->confirmRulesFormData($rules, $edit_url);
    }

  }

  /**
   * Add, edit and delete rules and making parameter.
   *
   * @param array $rules
   *   The array of rules.
   * @param bool $include_group
   *   Boolean to include group rule.
   *
   * @return array
   *   The edited params.
   *
   * @throws \Exception
   */
  protected function editRuleParams(array &$rules, $include_group = TRUE): array {
    $params = [];
    $inbound_index = 0;
    $outbound_index = 0;
    $del_idxs = array_rand($rules, random_int(1, count($rules)));
    if (!is_array($del_idxs)) {
      $del_idxs = [$del_idxs];
    }

    // We cannot rewrite like foreach ($rules ?: [] as $idx => &$rule) since
    // a variable &$rule is a reference.
    foreach ($rules as $idx => &$rule) {
      if ($rule['type'] === self::$openStackSecurityGroupRulesInbound) {
        if ($inbound_index === 0 && random_int(0, 1) === 1) {
          $rules[] = ['type' => self::$openStackSecurityGroupRulesInbound];
        }
        $index = $inbound_index++;
        $prefix = 'ip_permission';
      }
      else {
        if ($outbound_index === 0 && random_int(0, 1) === 1) {
          $rules[] = ['type' => self::$openStackSecurityGroupRulesOutbound];
        }
        $index = $outbound_index++;
        $prefix = 'outbound_permission';
      }
      if (in_array($idx, $del_idxs)) {
        foreach ($rule ?: [] as $key => $value) {
          if ($key === 'type' || $key === 'source') {
            continue;
          }
          $rule[$key] = '';
        }
      }
      else {
        $this->getRandomRule($rule, FALSE);
      }
      foreach ($rule ?: [] as $key => $value) {
        if ($key === 'type') {
          continue;
        }
        $params["${prefix}[${index}][${key}]"] = $value;
      }
    }

    $del_idxs = array_flip($del_idxs);
    $rules = array_diff_key($rules, $del_idxs);

    $this->updateRulesMockData($rules, self::$openStackSecurityGroupRulesOutbound);

    return $params;
  }

}
