<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s resource quota.
 *
 * @group K8s
 */
class K8sResourceQuotaTest extends K8sTestBase {

  public const K8S_RESOURCE_QUOTA_REPEAT_COUNT = 2;

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
      'list k8s resource quota',
      'view k8s resource quota',
      'edit k8s resource quota',
      'add k8s resource quota',
      'delete k8s resource quota',
      'view k8s namespace ' . $this->namespace,
    ];
  }

  /**
   * Tests CRUD for Resource Quota.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testResourceQuota(): void {

    $cloud_context = $this->cloudContext;

    // List Resource Quota for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/resource_quota");
    $this->assertNoErrorMessage();

    // Add a new Resource Quota.
    $add = $this->createResourceQuotaTestFormData(self::K8S_RESOURCE_QUOTA_REPEAT_COUNT, $this->namespace);
    for ($i = 0; $i < self::K8S_RESOURCE_QUOTA_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addResourceQuotaMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/resource_quota/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Resource Quota', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/resource_quota");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_RESOURCE_QUOTA_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all resource_quota listing exists.
      $this->drupalGet('/clouds/k8s/resource_quota');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Resource Quota.
    $edit = $this->createResourceQuotaTestFormData(self::K8S_RESOURCE_QUOTA_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_RESOURCE_QUOTA_REPEAT_COUNT; $i++, $num++) {

      $this->updateResourceQuotaMockData($edit[$i]);

      unset($edit[$i]['post_data']['namespace']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/resource_quota/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Resource Quota', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Resource Quota.
    for ($i = 0, $num = 1; $i < self::K8S_RESOURCE_QUOTA_REPEAT_COUNT; $i++, $num++) {

      $this->deleteResourceQuotaMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/resource_quota/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Resource Quota', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/resource_quota");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting resource quotas with bulk operation.
   *
   * @throws \Exception
   */
  public function testResourceQuotaBulk(): void {

    for ($i = 0; $i < self::K8S_RESOURCE_QUOTA_REPEAT_COUNT; $i++) {
      // Create Resource Quotas.
      $resource_quotas = $this->createResourceQuotasRandomTestFormData($this->namespace);
      $entities = [];
      foreach ($resource_quotas ?: [] as $resource_quota) {
        $entities[] = $this->createResourceQuotaTestEntity($resource_quota);
      }
      $this->deleteResourceQuotaMockData($resource_quotas[0]);
      $this->runTestEntityBulk('resource_quota', $entities);
    }
  }

}
