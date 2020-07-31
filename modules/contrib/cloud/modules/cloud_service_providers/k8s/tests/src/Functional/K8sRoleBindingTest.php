<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s role binding.
 *
 * @group K8s
 */
class K8sRoleBindingTest extends K8sTestBase {

  public const K8S_ROLE_BINDING_REPEAT_COUNT = 2;

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
      'list k8s role binding',
      'edit k8s role binding',
      'add k8s role binding',
      'delete k8s role binding',
      'view k8s namespace ' . $this->namespace,
    ];
  }

  /**
   * Tests CRUD for Role Binding.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testRoleBinding(): void {

    $cloud_context = $this->cloudContext;

    // List Role Binding for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/role_binding");
    $this->assertNoErrorMessage();

    // Add a new Role Binding.
    $add = $this->createRoleBindingTestFormData(self::K8S_ROLE_BINDING_REPEAT_COUNT, $this->namespace);
    for ($i = 0; $i < self::K8S_ROLE_BINDING_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addRoleBindingMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/role_binding/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Role Binding', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/role_binding");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_ROLE_BINDING_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all role_binding listing exists.
      $this->drupalGet('/clouds/k8s/role_binding');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Role Binding.
    $edit = $this->createRoleBindingTestFormData(self::K8S_ROLE_BINDING_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_ROLE_BINDING_REPEAT_COUNT; $i++, $num++) {

      $this->updateRoleBindingMockData($edit[$i]);

      unset($edit[$i]['post_data']['namespace']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/role_binding/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Role Binding', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete  Role Binding.
    for ($i = 0, $num = 1; $i < self::K8S_ROLE_BINDING_REPEAT_COUNT; $i++, $num++) {

      $this->deleteRoleBindingMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/role_binding/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Role Binding', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/role_binding");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting role bindings with bulk operation.
   *
   * @throws \Exception
   */
  public function testRoleBindingBulk(): void {

    for ($i = 0; $i < self::K8S_ROLE_BINDING_REPEAT_COUNT; $i++) {
      // Create  Roles Binding.
      $role_bindings = $this->createRoleBindingsRandomTestFormData();
      $entities = [];
      foreach ($role_bindings ?: [] as $role_binding) {
        $entities[] = $this->createRoleBindingTestEntity($role_binding);
      }
      $this->deleteRoleBindingMockData($role_bindings[0]);
      $this->runTestEntityBulk('role_binding', $entities);
    }
  }

}
