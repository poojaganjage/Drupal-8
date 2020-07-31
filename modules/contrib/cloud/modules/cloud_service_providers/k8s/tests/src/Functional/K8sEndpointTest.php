<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s endpoint.
 *
 * @group K8s
 */
class K8sEndpointTest extends K8sTestBase {

  public const K8S_ENDPOINT_REPEAT_COUNT = 2;

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
      'list k8s endpoint',
      'add k8s endpoint',
      'edit k8s endpoint',
      'delete k8s endpoint',
      'view k8s namespace ' . $this->namespace,
    ];
  }

  /**
   * Tests CRUD for Endpoint.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testEndpoint(): void {

    $cloud_context = $this->cloudContext;

    // List endpoint for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/endpoint");
    $this->assertNoErrorMessage();

    // Add a new endpoint.
    $add = $this->createEndpointTestFormData(self::K8S_ENDPOINT_REPEAT_COUNT, $this->namespace);
    for ($i = 0; $i < self::K8S_ENDPOINT_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addEndpointMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/endpoint/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Endpoint', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/endpoint");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_ENDPOINT_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all endpoint listing exists.
      $this->drupalGet('/clouds/k8s/endpoint');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Endpoint.
    $edit = $this->createEndpointTestFormData(self::K8S_ENDPOINT_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_ENDPOINT_REPEAT_COUNT; $i++, $num++) {

      $this->updateEndpointMockData($edit[$i]);

      unset($edit[$i]['post_data']['namespace']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/endpoint/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Endpoint', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Endpoint.
    for ($i = 0, $num = 1; $i < self::K8S_ENDPOINT_REPEAT_COUNT; $i++, $num++) {

      $this->deleteEndpointMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/endpoint/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Endpoint', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/endpoint");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting endpoint with bulk operation.
   *
   * @throws \Exception
   */
  public function testEndpointBulk(): void {

    for ($i = 0; $i < self::K8S_ENDPOINT_REPEAT_COUNT; $i++) {
      // Create Endpoint.
      $endpoints = $this->createEndpointsRandomTestFormData();
      $entities = [];
      foreach ($endpoints ?: [] as $endpoint) {
        $entities[] = $this->createEndpointTestEntity($endpoint);
      }
      $this->deleteEndpointMockData($endpoints[0]);
      $this->runTestEntityBulk('endpoint', $entities);
    }
  }

}
