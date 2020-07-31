<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s cluster role.
 *
 * @group K8s
 */
class K8sClusterRoleTest extends K8sTestBase {

  public const K8S_CLUSTER_ROLE_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list k8s cluster role',
      'view k8s cluster role',
      'edit k8s cluster role',
      'add k8s cluster role',
      'delete k8s cluster role',
    ];
  }

  /**
   * Tests CRUD for Cluster Role.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testClusterRole(): void {

    $cloud_context = $this->cloudContext;

    // List Cluster Role for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/cluster_role");
    $this->assertNoErrorMessage();

    // Add a new Cluster Role.
    $add = $this->createClusterRoleTestFormData(self::K8S_CLUSTER_ROLE_REPEAT_COUNT);
    for ($i = 0; $i < self::K8S_CLUSTER_ROLE_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addClusterRoleMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/cluster_role/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cluster Role', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/cluster_role");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_CLUSTER_ROLE_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all cluster_role listing exists.
      $this->drupalGet('/clouds/k8s/cluster_role');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Cluster Role.
    $edit = $this->createClusterRoleTestFormData(self::K8S_CLUSTER_ROLE_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::K8S_CLUSTER_ROLE_REPEAT_COUNT; $i++, $num++) {

      $this->updateClusterRoleMockData($edit[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/cluster_role/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cluster Role', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Cluster Role.
    for ($i = 0, $num = 1; $i < self::K8S_CLUSTER_ROLE_REPEAT_COUNT; $i++, $num++) {

      $this->deleteClusterRoleMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/cluster_role/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cluster Role', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/cluster_role");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting cluster roles with bulk operation.
   *
   * @throws \Exception
   */
  public function testClusterRoleBulk(): void {

    for ($i = 0; $i < self::K8S_CLUSTER_ROLE_REPEAT_COUNT; $i++) {
      // Create Cluster Roles.
      $cluster_roles = $this->createClusterRolesRandomTestFormData();
      $entities = [];
      foreach ($cluster_roles ?: [] as $cluster_role) {
        $entities[] = $this->createClusterRoleTestEntity($cluster_role);
      }
      $this->deleteClusterRoleMockData($cluster_roles[0]);
      $this->runTestEntityBulk('cluster_role', $entities);
    }
  }

}
