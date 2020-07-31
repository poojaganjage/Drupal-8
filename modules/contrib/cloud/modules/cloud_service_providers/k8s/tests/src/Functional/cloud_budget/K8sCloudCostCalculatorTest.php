<?php

namespace Drupal\Tests\k8s\Functional\cloud_budget;

use Drupal\Tests\k8s\Functional\K8sTestBase;

/**
 * Test K8s cloud cost calculator(K8sCloudCostCalculatorTest).
 *
 * @group Cloud
 */
class K8sCloudCostCalculatorTest extends K8sTestBase {

  public const K8S_CLOUD_COST_CALCULATOR_REPEAT_COUNT = 1;

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  protected function getPermissions():array {
    $namespaces = $this->createNamespacesRandomTestFormData();
    $this->createNamespaceTestEntity($namespaces[0]);
    $this->namespace = $namespaces[0]['name'];

    return [
      'list k8s pod',
      'add k8s pod',
      'view any k8s pod',
      'edit any k8s pod',
      'delete any k8s pod',
      'list k8s namespace',
      'view k8s namespace',
      'edit k8s namespace',
      'add k8s namespace',
      'delete k8s namespace',
      'launch cloud project',
      'add cloud projects',
      'list cloud project',
      'delete any cloud projects',
      'edit any cloud projects',
      'view any published cloud projects',
      'view any unpublished cloud projects',
      'access cloud project revisions',
      'revert all cloud project revisions',
      'delete all cloud project revisions',
    ];
  }

  /**
   * K8s cost storage test.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testK8sCostStorageTest(): void {
    $cloud_context = $this->cloudContext;
    $k8s_service = \Drupal::service('k8s');

    // Create entity.
    $nodes = $this->createNodeTestFormData(self::K8S_CLOUD_COST_CALCULATOR_REPEAT_COUNT);
    $pods = $this->createPodsRandomTestFormData($this->namespace);
    foreach ($pods ?: [] as $pod) {
      $this->createPodTestEntity($pod);
    }
    foreach ($nodes ?: [] as $node) {
      $this->createNodeTestEntity($node);
    }

    // Add mock data.
    $this->getNamespaceResourceUsageMockData($pods);
    $this->getNodeResourceUsageMockData($nodes);
    $this->calculateCostPerNamespaceMockData();

    // Get result.
    $result_node = $k8s_service->getNodeResourceUsage($cloud_context);
    $result_pod = $k8s_service->getNamespaceResourceUsage($cloud_context, $this->namespace);
    $result = $k8s_service->calculateCostPerNamespace($cloud_context, $this->namespace);

    $this->assertEqual($result['cpu_usage'], $result_pod['cpu_usage']);
    $this->assertEqual($result['memory_usage'], $result_pod['memory_usage']);
    $this->assertEqual($result['cpu_capacity'], $result_node['cpu_capacity']);
    $this->assertEqual($result['memory_capacity'], $result_node['memory_capacity']);
  }

}
