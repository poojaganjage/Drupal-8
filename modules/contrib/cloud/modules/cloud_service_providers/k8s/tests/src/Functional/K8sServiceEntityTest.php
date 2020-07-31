<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s service.
 *
 * @group K8s
 */
class K8sServiceEntityTest extends K8sTestBase {

  public const K8S_SERVICE_REPEAT_COUNT = 2;

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
      'list k8s service',
      'view k8s service',
      'edit k8s service',
      'add k8s service',
      'delete k8s service',
      'view k8s namespace ' . $this->namespace,
    ];
  }

  /**
   * Tests CRUD for Service.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testService(): void {

    $cloud_context = $this->cloudContext;

    // List Service for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/service");
    $this->assertNoErrorMessage();

    // Add a new Service.
    $add = $this->createServiceTestFormData(self::K8S_SERVICE_REPEAT_COUNT, $this->namespace);
    for ($i = 0; $i < self::K8S_SERVICE_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addServiceMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/service/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Service', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/service");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_SERVICE_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all service listing exists.
      $this->drupalGet('/clouds/k8s/service');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Service.
    $edit = $this->createServiceTestFormData(self::K8S_SERVICE_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_SERVICE_REPEAT_COUNT; $i++, $num++) {

      $this->updateServiceMockData($edit[$i]);

      unset($edit[$i]['post_data']['namespace']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/service/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Service', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Service.
    for ($i = 0, $num = 1; $i < self::K8S_SERVICE_REPEAT_COUNT; $i++, $num++) {

      $this->deleteServiceMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/service/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Service', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/service");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting services with bulk operation.
   *
   * @throws \Exception
   */
  public function testServiceBulk(): void {

    for ($i = 0; $i < self::K8S_SERVICE_REPEAT_COUNT; $i++) {
      // Create Services.
      $services = $this->createServicesRandomTestFormData($this->namespace);
      $entities = [];
      foreach ($services ?: [] as $service) {
        $entities[] = $this->createServiceTestEntity($service);
      }
      $this->deleteServiceMockData($services[0]);
      $this->runTestEntityBulk('service', $entities);
    }
  }

}
