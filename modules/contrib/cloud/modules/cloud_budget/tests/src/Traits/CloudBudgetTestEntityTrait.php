<?php

namespace Drupal\Tests\cloud_budget\Traits;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\cloud_budget\Entity\CloudCredit;
use Drupal\Tests\cloud\Traits\CloudTestEntityTrait;

/**
 * The trait creating test entity for cloud budget testing.
 */
trait CloudBudgetTestEntityTrait {

  use CloudTestEntityTrait;

  /**
   * Create a Cloud Credit test entity.
   *
   * @param array $cloud_credit
   *   The cloud credit data.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The cloud credit entity.
   */
  protected function createCloudCreditTestEntity(array $cloud_credit): CloudContentEntityBase {
    return $this->createTestEntity(CloudCredit::class, [
      'cloud_context' => $cloud_credit['cloud_context'],
      'user' => $cloud_credit['user'],
      'amount' => $cloud_credit['amount'],
      'refreshed' => time(),
    ]);
  }

  /**
   * Create a Cloud Cost Storage test entity.
   *
   * @param array $cloud_cost_storage
   *   The cloud cost storage data.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The cloud cost storage entity.
   */
  protected function createCloudCostStorageTestEntity(array $cloud_cost_storage): CloudContentEntityBase {
    return $this->createTestEntity(CloudCostStorage::class, [
      'cloud_context' => $cloud_cost_storage['cloud_context'],
      'group' => $cloud_cost_storage['group'],
      'cost' => $cloud_cost_storage['cost'],
      'refreshed' => time(),
    ]);
  }

}
