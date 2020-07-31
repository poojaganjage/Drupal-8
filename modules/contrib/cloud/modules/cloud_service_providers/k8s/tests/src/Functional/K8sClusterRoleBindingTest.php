<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s cluster role binding.
 *
 * @group K8s
 */
class K8sClusterRoleBindingTest extends K8sTestBase {

  public const K8S_CLUSTER_ROLE_BINDING_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list k8s cluster role binding',
      'view k8s cluster role binding',
      'edit k8s cluster role binding',
      'add k8s cluster role binding',
      'delete k8s cluster role binding',
    ];
  }

  /**
   * Tests CRUD for Cluster Role Binding.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testClusterRoleBinding(): void {

    $cloud_context = $this->cloudContext;

    // List Cluster Role Binding for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/cluster_role_binding");
    $this->assertNoErrorMessage();

    // Add a new Cluster Role Binding.
    $add = $this->createClusterRoleBindingTestFormData(self::K8S_CLUSTER_ROLE_BINDING_REPEAT_COUNT);
    for ($i = 0; $i < self::K8S_CLUSTER_ROLE_BINDING_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addClusterRoleBindingMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/cluster_role_binding/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cluster Role Binding', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/cluster_role_binding");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_CLUSTER_ROLE_BINDING_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all cluster_role_binding listing exists.
      $this->drupalGet('/clouds/k8s/cluster_role_binding');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Cluster Role Binding.
    $edit = $this->createClusterRoleBindingTestFormData(self::K8S_CLUSTER_ROLE_BINDING_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::K8S_CLUSTER_ROLE_BINDING_REPEAT_COUNT; $i++, $num++) {

      $this->updateClusterRoleBindingMockData($edit[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/cluster_role_binding/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cluster Role Binding', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Cluster Role Binding.
    for ($i = 0, $num = 1; $i < self::K8S_CLUSTER_ROLE_BINDING_REPEAT_COUNT; $i++, $num++) {

      $this->deleteClusterRoleBindingMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/cluster_role_binding/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cluster Role Binding', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/cluster_role_binding");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting cluster role bindings with bulk operation.
   *
   * @throws \Exception
   */
  public function testClusterRoleBindingBulk(): void {

    for ($i = 0; $i < self::K8S_CLUSTER_ROLE_BINDING_REPEAT_COUNT; $i++) {
      // Create Cluster Role Bindings.
      $cluster_role_bindings = $this->createClusterRoleBindingsRandomTestFormData();
      $entities = [];
      foreach ($cluster_role_bindings ?: [] as $cluster_role_binding) {
        $entities[] = $this->createClusterRoleBindingTestEntity($cluster_role_binding);
      }
      $this->deleteClusterRoleBindingMockData($cluster_role_bindings[0]);
      $this->runTestEntityBulk('cluster_role_binding', $entities);
    }
  }

}
