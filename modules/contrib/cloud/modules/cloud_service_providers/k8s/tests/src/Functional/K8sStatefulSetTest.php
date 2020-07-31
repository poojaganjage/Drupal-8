<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s stateful set.
 *
 * @group K8s
 */
class K8sStatefulSetTest extends K8sTestBase {

  public const K8S_STATEFUL_SET_REPEAT_COUNT = 2;

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
      'list k8s stateful set',
      'add k8s stateful set',
      'edit k8s stateful set',
      'delete k8s stateful set',
      'view k8s namespace ' . $this->namespace,
    ];
  }

  /**
   * Tests CRUD for Stateful Set.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testStatefulSet(): void {

    $cloud_context = $this->cloudContext;

    // List Stateful Set for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/stateful_set");
    $this->assertNoErrorMessage();

    // Add a new Stateful Set.
    $add = $this->createStatefulSetTestFormData(self::K8S_STATEFUL_SET_REPEAT_COUNT, $this->namespace);
    for ($i = 0; $i < self::K8S_STATEFUL_SET_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addStatefulSetMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/stateful_set/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Stateful Set', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/stateful_set");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_STATEFUL_SET_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all stateful_set listing exists.
      $this->drupalGet('/clouds/k8s/stateful_set');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Stateful Set.
    $edit = $this->createStatefulSetTestFormData(self::K8S_STATEFUL_SET_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_STATEFUL_SET_REPEAT_COUNT; $i++, $num++) {

      $this->updateStatefulSetMockData($edit[$i]);

      unset($edit[$i]['post_data']['namespace']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/stateful_set/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Stateful Set', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Stateful Set.
    for ($i = 0, $num = 1; $i < self::K8S_STATEFUL_SET_REPEAT_COUNT; $i++, $num++) {

      $this->deleteStatefulSetMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/stateful_set/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Stateful Set', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/stateful_set");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting stateful set with bulk operation.
   *
   * @throws \Exception
   */
  public function testStatefulSetBulk(): void {

    for ($i = 0; $i < self::K8S_STATEFUL_SET_REPEAT_COUNT; $i++) {
      // Create Stateful Set.
      $stateful_sets = $this->createStatefulSetsRandomTestFormData();
      $entities = [];
      foreach ($stateful_sets ?: [] as $stateful_set) {
        $entities[] = $this->createStatefulSetTestEntity($stateful_set);
      }
      $this->deleteStatefulSetMockData($stateful_sets[0]);
      $this->runTestEntityBulk('stateful_set', $entities);
    }
  }

}
