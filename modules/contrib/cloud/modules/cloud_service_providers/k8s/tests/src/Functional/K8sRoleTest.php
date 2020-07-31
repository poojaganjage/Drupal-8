<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s role.
 *
 * @group K8s
 */
class K8sRoleTest extends K8sTestBase {

  public const K8S_ROLE_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  protected function getPermissions(): array {
    $namespaces = $this->createNamespacesRandomTestFormData();
    $this->createNamespaceTestEntity($namespaces[0]);
    $this->namespace = $namespaces[0]['name'];

    return [
      'list k8s role',
      'view k8s role',
      'edit k8s role',
      'add k8s role',
      'delete k8s role',
      'view k8s namespace ' . $this->namespace,
    ];
  }

  /**
   * Tests CRUD for Role.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testRole(): void {

    $cloud_context = $this->cloudContext;

    // List Role for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/role");
    $this->assertNoErrorMessage();

    // Add a new Role.
    $add = $this->createRoleTestFormData(self::K8S_ROLE_REPEAT_COUNT, $this->namespace);
    for ($i = 0; $i < self::K8S_ROLE_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addRoleMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/role/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Role', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/role");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_ROLE_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all role listing exists.
      $this->drupalGet('/clouds/k8s/role');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Role.
    $edit = $this->createRoleTestFormData(self::K8S_ROLE_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_ROLE_REPEAT_COUNT; $i++, $num++) {

      $this->updateRoleMockData($edit[$i]);

      unset($edit[$i]['post_data']['namespace']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/role/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Role', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Role.
    for ($i = 0, $num = 1; $i < self::K8S_ROLE_REPEAT_COUNT; $i++, $num++) {

      $this->deleteRoleMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/role/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Role', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/role");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting roles with bulk operation.
   *
   * @throws \Exception
   */
  public function testRoleBulk(): void {

    for ($i = 0; $i < self::K8S_ROLE_REPEAT_COUNT; $i++) {
      // Create Roles.
      $roles = $this->createRolesRandomTestFormData($this->namespace);
      $entities = [];
      foreach ($roles ?: [] as $role) {
        $entities[] = $this->createRoleTestEntity($role);
      }
      $this->deleteRoleMockData($roles[0]);
      $this->runTestEntityBulk('role', $entities);
    }
  }

}
