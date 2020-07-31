<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s replica set.
 *
 * @group K8s
 */
class K8sReplicaSetTest extends K8sTestBase {

  public const K8S_REPLICA_SET_REPEAT_COUNT = 2;

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
      'list k8s replica set',
      'view k8s replica set',
      'edit k8s replica set',
      'add k8s replica set',
      'delete k8s replica set',
      'view k8s namespace ' . $this->namespace,
    ];
  }

  /**
   * Tests CRUD for Replica Set.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testReplicaSet(): void {

    $cloud_context = $this->cloudContext;

    // List Replica Set for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/replica_set");
    $this->assertNoErrorMessage();

    // Add a new Replica Set.
    $add = $this->createReplicaSetTestFormData(self::K8S_REPLICA_SET_REPEAT_COUNT, $this->namespace);
    for ($i = 0; $i < self::K8S_REPLICA_SET_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addReplicaSetMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/replica_set/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Replica Set', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/replica_set");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_REPLICA_SET_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all replica_set listing exists.
      $this->drupalGet('/clouds/k8s/replica_set');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Replica Set.
    $edit = $this->createReplicaSetTestFormData(self::K8S_REPLICA_SET_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_REPLICA_SET_REPEAT_COUNT; $i++, $num++) {

      $this->updateReplicaSetMockData($edit[$i]);

      unset($edit[$i]['post_data']['namespace']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/replica_set/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Replica Set', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Replica Set.
    for ($i = 0, $num = 1; $i < self::K8S_REPLICA_SET_REPEAT_COUNT; $i++, $num++) {

      $this->deleteReplicaSetMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/replica_set/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Replica Set', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/replica_set");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting replica sets with bulk operation.
   *
   * @throws \Exception
   */
  public function testReplicaSetBulk(): void {

    for ($i = 0; $i < self::K8S_REPLICA_SET_REPEAT_COUNT; $i++) {
      // Create Replica Sets.
      $replica_sets = $this->createReplicaSetsRandomTestFormData($this->namespace);
      $entities = [];
      foreach ($replica_sets ?: [] as $replica_set) {
        $entities[] = $this->createReplicaSetTestEntity($replica_set);
      }
      $this->deleteReplicaSetMockData($replica_sets[0]);
      $this->runTestEntityBulk('replica_set', $entities);
    }
  }

}
