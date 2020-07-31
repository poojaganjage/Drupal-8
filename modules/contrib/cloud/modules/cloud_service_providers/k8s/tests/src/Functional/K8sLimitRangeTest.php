<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s limit range.
 *
 * @group K8s
 */
class K8sLimitRangeTest extends K8sTestBase {

  public const K8S_LIMIT_RANGE_REPEAT_COUNT = 2;

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
      'list k8s limit range',
      'view k8s limit range',
      'edit k8s limit range',
      'add k8s limit range',
      'delete k8s limit range',
      'view k8s namespace ' . $this->namespace,
    ];
  }

  /**
   * Tests CRUD for Limit Range.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testLimitRange(): void {

    $cloud_context = $this->cloudContext;

    // List Limit Range for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/limit_range");
    $this->assertNoErrorMessage();

    // Add a new Limit Range.
    $add = $this->createLimitRangeTestFormData(self::K8S_LIMIT_RANGE_REPEAT_COUNT, $this->namespace);
    for ($i = 0; $i < self::K8S_LIMIT_RANGE_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addLimitRangeMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/limit_range/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Limit Range', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/limit_range");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_LIMIT_RANGE_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all limit_range listing exists.
      $this->drupalGet('/clouds/k8s/limit_range');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Limit Range.
    $edit = $this->createLimitRangeTestFormData(self::K8S_LIMIT_RANGE_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_LIMIT_RANGE_REPEAT_COUNT; $i++, $num++) {

      $this->updateLimitRangeMockData($edit[$i]);

      unset($edit[$i]['post_data']['namespace']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/limit_range/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Limit Range', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Limit Range.
    for ($i = 0, $num = 1; $i < self::K8S_LIMIT_RANGE_REPEAT_COUNT; $i++, $num++) {

      $this->deleteLimitRangeMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/limit_range/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Limit Range', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/limit_range");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting limit ranges with bulk operation.
   *
   * @throws \Exception
   */
  public function testLimitRangeBulk(): void {

    for ($i = 0; $i < self::K8S_LIMIT_RANGE_REPEAT_COUNT; $i++) {
      // Create Limit Ranges.
      $limit_ranges = $this->createLimitRangesRandomTestFormData($this->namespace);
      $entities = [];
      foreach ($limit_ranges ?: [] as $limit_range) {
        $entities[] = $this->createLimitRangeTestEntity($limit_range);
      }
      $this->deleteLimitRangeMockData($limit_ranges[0]);
      $this->runTestEntityBulk('limit_range', $entities);
    }
  }

}
