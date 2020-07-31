<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s namespace.
 *
 * @group K8s
 */
class K8sNamespaceTest extends K8sTestBase {

  public const K8S_NAMESPACE_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list k8s namespace',
      'view k8s namespace',
      'edit k8s namespace',
      'add k8s namespace',
      'delete k8s namespace',
    ];
  }

  /**
   * Tests CRUD for Namespace.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testNamespace() {

    $cloud_context = $this->cloudContext;

    // List Namespace for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/namespace");
    $this->assertNoErrorMessage();

    // Add a new Namespace.
    $add = $this->createNamespaceTestFormData(self::K8S_NAMESPACE_REPEAT_COUNT);
    for ($i = 0; $i < self::K8S_NAMESPACE_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addNamespaceMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/namespace/add",
        $add[$i],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Namespace', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/namespace");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_NAMESPACE_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all namespace listing exists.
      $this->drupalGet('/clouds/k8s/namespace');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Namespace.
    $edit = $this->createNamespaceTestFormData(self::K8S_NAMESPACE_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::K8S_NAMESPACE_REPEAT_COUNT; $i++, $num++) {

      $this->updateNamespaceMockData($edit[$i]);

      unset($edit[$i]['name']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/namespace/$num/edit",
        $edit[$i],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Namespace', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      // Make sure labels.
      $this->drupalGet("/clouds/k8s/$cloud_context/namespace/$num");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($edit[$i]['labels[0][item_key]']);
      $this->assertSession()->pageTextContains($edit[$i]['labels[0][item_value]']);
      $this->assertSession()->pageTextContains($edit[$i]['annotations[0][item_key]']);
      $this->assertSession()->pageTextContains($edit[$i]['annotations[0][item_value]']);
    }

    // Delete Namespace.
    for ($i = 0, $num = 1; $i < self::K8S_NAMESPACE_REPEAT_COUNT; $i++, $num++) {

      $this->deleteNamespaceMockData($add[$i]);

      $this->drupalGet("/clouds/k8s/$cloud_context/namespace/$num/delete");
      $this->assertNoErrorMessage();

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/namespace/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Namespace', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/namespace");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting namespaces with bulk operation.
   *
   * @throws \Exception
   */
  public function testNamespaceBulk() {

    for ($i = 0; $i < self::K8S_NAMESPACE_REPEAT_COUNT; $i++) {
      // Create Namespaces.
      $namespaces = $this->createNamespacesRandomTestFormData();
      $entities = [];
      foreach ($namespaces ?: [] as $namespace) {
        $entities[] = $this->createNamespaceTestEntity($namespace);
      }
      $this->deleteNamespaceMockData($namespaces[0]);
      $this->runTestEntityBulk('namespace', $entities);
    }
  }

}
