<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s daemon set.
 *
 * @group K8s
 */
class K8sDaemonSetTest extends K8sTestBase {

  public const K8S_DAEMON_SET_REPEAT_COUNT = 2;

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
      'list k8s daemon set',
      'add k8s daemon set',
      'edit k8s daemon set',
      'delete k8s daemon set',
      'view k8s namespace ' . $this->namespace,
    ];
  }

  /**
   * Tests CRUD for Daemon Set.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testDaemonSet(): void {

    $cloud_context = $this->cloudContext;

    // List Daemon Set for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/daemon_set");
    $this->assertNoErrorMessage();

    // Add a new Daemon Set.
    $add = $this->createDaemonSetTestFormData(self::K8S_DAEMON_SET_REPEAT_COUNT, $this->namespace);
    for ($i = 0; $i < self::K8S_DAEMON_SET_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addDaemonSetMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/daemon_set/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Daemon Set', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/daemon_set");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_DAEMON_SET_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all daemon_set listing exists.
      $this->drupalGet('/clouds/k8s/daemon_set');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Daemon Set.
    $edit = $this->createDaemonSetTestFormData(self::K8S_DAEMON_SET_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_DAEMON_SET_REPEAT_COUNT; $i++, $num++) {

      $this->updateDaemonSetMockData($edit[$i]);

      unset($edit[$i]['post_data']['namespace']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/daemon_set/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Daemon Set', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Daemon Set.
    for ($i = 0, $num = 1; $i < self::K8S_DAEMON_SET_REPEAT_COUNT; $i++, $num++) {

      $this->deleteDaemonSetMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/daemon_set/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Daemon Set', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/daemon_set");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting daemon set with bulk operation.
   *
   * @throws \Exception
   */
  public function testDaemonSetBulk(): void {

    for ($i = 0; $i < self::K8S_DAEMON_SET_REPEAT_COUNT; $i++) {
      // Create Daemon Set.
      $daemon_sets = $this->createDaemonSetsRandomTestFormData();
      $entities = [];
      foreach ($daemon_sets ?: [] as $daemon_set) {
        $entities[] = $this->createDaemonSetTestEntity($daemon_set);
      }
      $this->deleteDaemonSetMockData($daemon_sets[0]);
      $this->runTestEntityBulk('daemon_set', $entities);
    }
  }

}
