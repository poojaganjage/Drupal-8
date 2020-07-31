<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s Network Policy.
 *
 * @group K8s
 */
class K8sNetworkPolicyTest extends K8sTestBase {

  public const K8S_NETWORK_POLICY_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  protected function getPermissions(): array {
    $namespaces = $this->createNamespacesRandomTestFormData();
    $this->createNamespaceTestEntity($namespaces[0]);
    $this->namespace = $namespaces[0]['name'];

    return [
      'list k8s network policy',
      'view k8s network policy',
      'edit k8s network policy',
      'add k8s network policy',
      'delete k8s network policy',
      'view k8s namespace ' . $this->namespace,
    ];
  }

  /**
   * Tests CRUD for Network Policy.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testNetworkPolicy() {

    $cloud_context = $this->cloudContext;

    // List Network Policy for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/network_policy");
    $this->assertNoErrorMessage();

    // Add a new Network Policy.
    $add = $this->createNetworkPolicyTestFormData(self::K8S_NETWORK_POLICY_REPEAT_COUNT, $this->namespace);
    for ($i = 0; $i < self::K8S_NETWORK_POLICY_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addNetworkPolicyMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/network_policy/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Network Policy', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/network_policy");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_NETWORK_POLICY_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all network_policy listing exists.
      $this->drupalGet('/clouds/k8s/network_policy');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Network Policy.
    $edit = $this->createNetworkPolicyTestFormData(self::K8S_NETWORK_POLICY_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_NETWORK_POLICY_REPEAT_COUNT; $i++, $num++) {

      $this->updateNetworkPolicyMockData($edit[$i]);

      unset($edit[$i]['post_data']['namespace']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/network_policy/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Network Policy', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Network Policy.
    for ($i = 0, $num = 1; $i < self::K8S_NETWORK_POLICY_REPEAT_COUNT; $i++, $num++) {

      $this->deleteNetworkPolicyMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/network_policy/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Network Policy', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/network_policy");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting Network Policies with bulk operation.
   *
   * @throws \Exception
   */
  public function testNetworkPolicyBulk() {

    for ($i = 0; $i < self::K8S_NETWORK_POLICY_REPEAT_COUNT; $i++) {
      // Create Network Policies.
      $network_policies = $this->createNetworkPoliciesRandomTestFormData($this->namespace);
      $entities = [];
      foreach ($network_policies ?: [] as $network_policy) {
        $entities[] = $this->createNetworkPolicyTestEntity($network_policy);
      }
      $this->deleteNetworkPolicyMockData($network_policies[0]);
      $this->runTestEntityBulk('network_policy', $entities);
    }
  }

}
