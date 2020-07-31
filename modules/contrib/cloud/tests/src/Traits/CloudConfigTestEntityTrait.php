<?php

namespace Drupal\Tests\cloud\Traits;

use Drupal\cloud\Entity\CloudConfig;
use Drupal\cloud\Entity\CloudContentEntityBase;

/**
 * The trait creating test entity for openstack testing.
 */
trait CloudConfigTestEntityTrait {

  use CloudTestEntityTrait;

  /**
   * Create a cloud service provider (CloudConfig) test entity.
   *
   * @param string $bundle
   *   The CloudConfig bundle Type string.
   * @param int $index
   *   The index.
   * @param string $cloud_context
   *   The Cloud Context.
   * @param string $cloud_config_name
   *   The cloud service provider (CloudConfig) name.
   *
   * @return \Drupal\cloud\Entity\CloudConfig
   *   The cloud service provider (CloudConfig) entity.
   */
  protected function createCloudConfigTestEntity($bundle, $index = 0, $cloud_context = '', $cloud_config_name = ''): CloudContentEntityBase {
    return $this->createTestEntity(CloudConfig::class, [
      'type'          => $bundle,
      'cloud_context' => $cloud_context,
      'name'          => $cloud_config_name ?: sprintf('config-entity-#%d-%s - %s', $index + 1, date('Y/m/d H:i:s'), $this->random->name(4, TRUE)),
    ]);
  }

}
