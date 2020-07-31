<?php

namespace Drupal\Tests\cloud\Functional\cloud\config;

use Drupal\cloud\Entity\CloudConfig;
use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Tests\cloud\Functional\CloudTestBase;
use Drupal\Tests\cloud\Traits\CloudConfigTestEntityTrait;
use Drupal\Tests\cloud\Traits\CloudConfigTestFormDataTrait;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Test case base class for a cloud service provider (CloudConfig).
 */
abstract class CloudConfigTestBase extends CloudTestBase {

  use CloudConfigTestEntityTrait;
  use CloudConfigTestFormDataTrait;

  /**
   * Create cloud context.
   *
   * @param string $bundle
   *   The CloudConfig Bundle Type. e.g. aws_cloud, k8s, openstack or terraform.
   *
   * @return \Drupal\cloud\Entity\CloudConfig
   *   The cloud service provider (CloudConfig) entity.
   */
  protected function createCloudContext($bundle): CloudContentEntityBase {
    assert(!empty($bundle), 'The parameter $bundle needs to have an entity bundle type string.');
    $random = $this->random;
    $this->cloudContext = $random->name(8);
    return $this->createTestEntity(CloudConfig::class, [
      'type'             => $bundle,
      'cloud_context'    => $this->cloudContext,
      'name'             => "$bundle - {$random->name(8, TRUE)}",
    ]);
  }

  /**
   * Test bulk operation for entities.
   *
   * @param string $type
   *   The name of the bundle. For example, aws_cloud, k8s or openstack.
   * @param array $entities
   *   The data of entities.
   * @param string $operation
   *   The operation.
   * @param string $passive_operation
   *   The passive voice of operation.
   * @param string $path_prefix
   *   The URL path of prefix.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Exception
   */
  protected function runTestEntityBulk(
    $type,
    array $entities = [],
    $operation = 'delete',
    $passive_operation = 'deleted',
    $path_prefix = '/admin/structure'): void {

    // Create cloud service providers.
    $cloud_configs = $this->createCloudConfigRandomTestFormData();
    $index = 0;
    foreach ($cloud_configs ?: [] as $cloud_config) {
      $entity = $this->createCloudConfigTestEntity($type, $index++, $cloud_config['CloudContext'], $cloud_config['Name']);

      $this->grantPermissions(
        Role::load(
          RoleInterface::AUTHENTICATED_ID),
        ["view {$entity->getCloudContext()}"]
      );
      $entities[] = $entity;
    }

    $this->drupalGet('/admin/structure/cloud_config');
    $this->runTestEntityBulkImpl(
      'cloud_config',
      $entities,
      $operation,
      $passive_operation,
      $path_prefix
    );

    $this->drupalGet('/admin/structure/cloud_config');
    foreach ($cloud_configs ?: [] as $cloud_config) {
      $this->assertSession()->linkNotExists($cloud_config['Name']);
    }

    $this->drupalGet('/clouds');
    foreach ($cloud_configs ?: [] as $cloud_config) {
      $this->assertSession()->linkNotExists($cloud_config['Name']);
    }
  }

  /**
   * Repeating test cloud service provider redirect.
   *
   * @param int $max_count
   *   Max test repeating count.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  protected function repeatTestCloudConfigRedirect($max_count): void {
    $paths = [
      '/clouds',
      '/clouds/design',
    ];

    $url = Url::fromRoute('entity.cloud_config.add_page');
    $page_link = Link::fromTextAndUrl(t('cloud service provider'), $url)
      ->toString();

    foreach ($paths ?: [] as $path) {
      for ($i = 0; $i < $max_count; $i++) {
        $this->drupalGet($path);
        $this->assertNoErrorMessage();

        $this->assertSession()->pageTextContains($this->t('Add cloud service provider'));
        $this->assertRaw(
          $this->t('There is no cloud service provider. Please create a new @link.', [
            '@link' => $page_link,
          ])
        );
      }
    }
  }

}
