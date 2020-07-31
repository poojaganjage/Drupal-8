<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s ConfigNap.
 *
 * @group K8s
 */
class K8sConfigMapTest extends K8sTestBase {

  public const K8S_CONFIG_MAP_REPEAT_COUNT = 2;

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
      'list k8s configmap',
      'view k8s configmap',
      'edit k8s configmap',
      'add k8s configmap',
      'delete k8s configmap',
      'view k8s namespace ' . $this->namespace,
    ];
  }

  /**
   * Tests CRUD for ConfigMap.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testConfigMap(): void {

    $cloud_context = $this->cloudContext;

    // List ConfigMap for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/config_map");
    $this->assertNoErrorMessage();

    // Add a new ConfigMap.
    $add = $this->createConfigMapTestFormData(self::K8S_CONFIG_MAP_REPEAT_COUNT, $this->namespace);
    for ($i = 0; $i < self::K8S_CONFIG_MAP_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addConfigMapMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/config_map/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'ConfigMap', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/config_map");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_CONFIG_MAP_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all config_map listing exists.
      $this->drupalGet('/clouds/k8s/config_map');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a ConfigMap.
    $edit = $this->createConfigMapTestFormData(self::K8S_CONFIG_MAP_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_CONFIG_MAP_REPEAT_COUNT; $i++, $num++) {

      $this->updateConfigMapMockData($edit[$i]);

      unset($edit[$i]['post_data']['namespace']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/config_map/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'ConfigMap', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete ConfigMap.
    for ($i = 0, $num = 1; $i < self::K8S_CONFIG_MAP_REPEAT_COUNT; $i++, $num++) {

      $this->deleteConfigMapMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/config_map/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'ConfigMap', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/config_map");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting ConfigMaps with bulk operation.
   *
   * @throws \Exception
   */
  public function testConfigMapBulk(): void {

    for ($i = 0; $i < self::K8S_CONFIG_MAP_REPEAT_COUNT; $i++) {
      // Create ConfigMaps.
      $config_maps = $this->createConfigMapsRandomTestFormData($this->namespace);
      $entities = [];
      foreach ($config_maps ?: [] as $config_map) {
        $entities[] = $this->createConfigMapTestEntity($config_map);
      }
      $this->deleteConfigMapMockData($config_maps[0]);
      $this->runTestEntityBulk('config_map', $entities);
    }
  }

}
