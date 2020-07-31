<?php

namespace Drupal\Tests\cloud_budget\Functional;

/**
 * Tests cloud cost storage.
 *
 * @group Cloud
 */
class CloudCostStorageTest extends CloudBudgetTestBase {

  public const CLOUD_COST_STORAGE_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list cloud cost storage',
      'view cloud cost storage',
      'edit cloud cost storage',
      'add cloud cost storage',
      'delete cloud cost storage',
    ];
  }

  /**
   * Tests CRUD for Cloud Cost Storage.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testCloudCostStorage(): void {

    // Add a new Cloud Cost Storage.
    $add = $this->createCloudCostStorageRandomTestFormData(self::CLOUD_COST_STORAGE_REPEAT_COUNT);
    for ($i = 0; $i < self::CLOUD_COST_STORAGE_REPEAT_COUNT; $i++) {
      $this->reloadMockData();
      $entity = \Drupal::entityTypeManager()->getStorage('cloud_cost_storage')->create($add[$i]);
      $entity->save();
    }

    // Check whether to exist or not.
    $count = 0;
    $entities = \Drupal::entityTypeManager()
      ->getStorage('cloud_cost_storage')
      ->loadByProperties([]);
    foreach ($entities as $entity) {
      $this->assertEqual($entity->getPayer(), $add[$count]['payer']);
      $this->assertEqual($entity->getCost(), $add[$count]['cost']);
      $count += 1;
    }

    // Delete entities.
    foreach ($entities as $entity) {
      $entity->delete();
    }
    $entities = \Drupal::entityTypeManager()
      ->getStorage('cloud_cost_storage')
      ->loadByProperties([]);
    $this->assertEqual(empty($entities), TRUE);
  }

}
