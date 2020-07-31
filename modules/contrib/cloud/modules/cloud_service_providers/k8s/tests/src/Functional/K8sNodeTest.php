<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s node.
 *
 * @group K8s
 */
class K8sNodeTest extends K8sTestBase {

  public const K8S_NODE_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list k8s node',
      'view k8s node',
    ];
  }

  /**
   * Tests CRUD for K8s node.
   *
   * @throws \Exception
   */
  public function testNode() {
    $cloud_context = $this->cloudContext;

    $data = $this->createNodeTestFormData(self::K8S_NODE_REPEAT_COUNT);
    $this->updateNodesMockData($data);

    // Update k8s nodes.
    $this->drupalGet("/clouds/k8s/$cloud_context/node/update");
    $this->assertNoErrorMessage();

    for ($i = 0; $i < self::K8S_NODE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($data[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_NODE_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all node listing exists.
      $this->drupalGet('/clouds/k8s/node');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($data[$j]['name']);
      }
    }
  }

}
