<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s API Service.
 *
 * @group K8s
 */
class K8sApiServiceTest extends K8sTestBase {

  public const K8S_API_SERVICE_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list k8s api service',
      'add k8s api service',
      'edit k8s api service',
      'delete k8s api service',
      'view k8s api service',
    ];
  }

  /**
   * Tests CRUD for API Service.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testApiService(): void {

    $cloud_context = $this->cloudContext;

    // List API Service for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/api_service");
    $this->assertNoErrorMessage();

    // Add a new API Service.
    $add = $this->createApiServiceTestFormData(self::K8S_API_SERVICE_REPEAT_COUNT);

    for ($i = 0; $i < self::K8S_API_SERVICE_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addApiServiceMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/api_service/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'API Service', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/api_service");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_API_SERVICE_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all api_service listing exists.
      $this->drupalGet('/clouds/k8s/api_service');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a API Service.
    $edit = $this->createApiServiceTestFormData(self::K8S_API_SERVICE_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::K8S_API_SERVICE_REPEAT_COUNT; $i++, $num++) {

      $this->updateApiServiceMockData($edit[$i]);

      // unset($edit[$i]['post_data']['namespace']);.
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/api_service/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'API Service', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

    }

    // Delete API Service.
    for ($i = 0, $num = 1; $i < self::K8S_API_SERVICE_REPEAT_COUNT; $i++, $num++) {

      $this->deleteApiServiceMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/api_service/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'API Service', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/api_service");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting API response with bulk operation.
   *
   * @throws \Exception
   */
  public function testApiServiceBulk(): void {

    for ($i = 0; $i < self::K8S_API_SERVICE_REPEAT_COUNT; $i++) {
      // Create API Service.
      $api_services = $this->createApiServicesRandomTestFormData();
      $entities = [];
      foreach ($api_services ?: [] as $api_service) {
        $entities[] = $this->createApiServiceTestEntity($api_service);
      }
      $this->deleteApiServiceMockData($api_service);
      $this->runTestEntityBulk('api_service', $entities);
    }
  }

}
