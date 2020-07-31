<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s pod.
 *
 * @group K8s
 */
class K8sPodTest extends K8sTestBase {

  public const K8S_POD_REPEAT_COUNT = 2;

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
      'list k8s pod',
      'add k8s pod',
      'view any k8s pod',
      'edit any k8s pod',
      'delete any k8s pod',
      'view k8s namespace ' . $this->namespace,
    ];
  }

  /**
   * Tests CRUD for Pod.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testPod() {

    $cloud_context = $this->cloudContext;

    // List Pod for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/pod");
    $this->assertNoErrorMessage();

    // Add a new Pod.
    $add = $this->createPodTestFormData(self::K8S_POD_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_POD_REPEAT_COUNT; $i++, $num++) {
      $this->reloadMockData();

      $this->addPodMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/pod/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Pod', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/pod");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);

      // Assert logs.
      $logs = $this->random->name(128, TRUE);
      $this->getPodLogsMockData($logs);
      $this->drupalGet("/clouds/k8s/$cloud_context/pod/$num/log");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($logs);
    }

    for ($i = 0, $num = 1; $i < self::K8S_POD_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all pod listing exists.
      $this->drupalGet('/clouds/k8s/pod');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Pod.
    $edit = $this->createPodTestFormData(self::K8S_POD_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_POD_REPEAT_COUNT; $i++, $num++) {

      $this->updatePodMockData($edit[$i]);

      unset($edit[$i]['post_data']['namespace']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/pod/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Pod', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Pod.
    for ($i = 0, $num = 1; $i < self::K8S_POD_REPEAT_COUNT; $i++, $num++) {

      $this->deletePodMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/pod/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Pod', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/pod");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting pods with bulk operation.
   *
   * @throws \Exception
   */
  public function testPodBulk() {

    for ($i = 0; $i < self::K8S_POD_REPEAT_COUNT; $i++) {
      // Create Pods.
      $pods = $this->createPodsRandomTestFormData($this->namespace);
      $entities = [];
      foreach ($pods ?: [] as $pod) {
        $entities[] = $this->createPodTestEntity($pod);
      }
      $this->deletePodMockData($pods[0]);
      $this->runTestEntityBulk('pod', $entities);
    }
  }

}
