<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s ingress.
 *
 * @group K8s
 */
class K8sIngressTest extends K8sTestBase {

  public const K8S_INGRESS_REPEAT_COUNT = 2;

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
      'list k8s ingress',
      'add k8s ingress',
      'edit k8s ingress',
      'delete k8s ingress',
      'view k8s namespace ' . $this->namespace,
    ];
  }

  /**
   * Tests CRUD for Ingress.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testIngress(): void {

    $cloud_context = $this->cloudContext;

    // List Ingress for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/ingress");
    $this->assertNoErrorMessage();

    // Add a new Ingress.
    $add = $this->createIngressTestFormData(self::K8S_INGRESS_REPEAT_COUNT, $this->namespace);
    for ($i = 0; $i < self::K8S_INGRESS_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addIngressMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/ingress/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Ingress', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/ingress");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_INGRESS_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all ingress listing exists.
      $this->drupalGet('/clouds/k8s/ingress');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Ingress.
    $edit = $this->createIngressTestFormData(self::K8S_INGRESS_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_INGRESS_REPEAT_COUNT; $i++, $num++) {

      $this->updateIngressMockData($edit[$i]);

      unset($edit[$i]['post_data']['namespace']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/ingress/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Ingress', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Ingress.
    for ($i = 0, $num = 1; $i < self::K8S_INGRESS_REPEAT_COUNT; $i++, $num++) {

      $this->deleteIngressMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/ingress/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Ingress', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/ingress");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting ingress with bulk operation.
   *
   * @throws \Exception
   */
  public function testIngressBulk(): void {

    for ($i = 0; $i < self::K8S_INGRESS_REPEAT_COUNT; $i++) {
      // Create Ingress.
      $ingresses = $this->createIngressesRandomTestFormData();
      $entities = [];
      foreach ($ingresses ?: [] as $ingress) {
        $entities[] = $this->createIngressTestEntity($ingress);
      }
      $this->deleteIngressMockData($ingresses[0]);
      $this->runTestEntityBulk('ingress', $entities);
    }
  }

}
