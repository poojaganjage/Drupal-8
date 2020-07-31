<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s service account.
 *
 * @group K8s
 */
class K8sServiceAccountTest extends K8sTestBase {

  public const K8S_SERVICE_ACCOUNT_REPEAT_COUNT = 3;

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
      'list k8s service account',
      'add k8s service account',
      'edit k8s service account',
      'delete k8s service account',
      'view k8s namespace ' . $this->namespace,
    ];
  }

  /**
   * Tests CRUD for Service account.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testServiceAccount(): void {

    $cloud_context = $this->cloudContext;

    // List Service Account for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/service_account");
    $this->assertNoErrorMessage();

    // Add a new Service Account.
    $add = $this->createServiceAccountTestFormData(self::K8S_SERVICE_ACCOUNT_REPEAT_COUNT, $this->namespace);
    for ($i = 0; $i < self::K8S_SERVICE_ACCOUNT_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addServiceAccountMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/service_account/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Service Account', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/service_account");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_SERVICE_ACCOUNT_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all service_account listing exists.
      $this->drupalGet('/clouds/k8s/service_account');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Service Account.
    $edit = $this->createServiceAccountTestFormData(self::K8S_SERVICE_ACCOUNT_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_SERVICE_ACCOUNT_REPEAT_COUNT; $i++, $num++) {

      $this->updateServiceAccountMockData($edit[$i]);

      unset($edit[$i]['post_data']['namespace']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/service_account/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Service Account', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Service Account.
    for ($i = 0, $num = 1; $i < self::K8S_SERVICE_ACCOUNT_REPEAT_COUNT; $i++, $num++) {

      $this->deleteServiceAccountMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/service_account/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Service Account', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));;

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/service_account");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting Service Account with bulk operation.
   *
   * @throws \Exception
   */
  public function testServiceAccountBulk(): void {

    for ($i = 0; $i < self::K8S_SERVICE_ACCOUNT_REPEAT_COUNT; $i++) {
      // Create Service Account.
      $service_accounts = $this->createServiceAccountsRandomTestFormData();
      $entities = [];
      foreach ($service_accounts ?: [] as $service_account) {
        $entities[] = $this->createServiceAccountTestEntity($service_account);
      }
      $this->deleteServiceAccountMockData($service_accounts[0]);
      $this->runTestEntityBulk('service_account', $entities);
    }
  }

}
