<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s persistent volume.
 *
 * @group K8s
 */
class K8sPersistentVolumeTest extends K8sTestBase {

  public const K8S_PERSISTENT_VOLUME_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list k8s persistent volume',
      'view k8s persistent volume',
      'edit k8s persistent volume',
      'add k8s persistent volume',
      'delete k8s persistent volume',
    ];
  }

  /**
   * Tests CRUD for persistent volume.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testPersistentVolume(): void {

    $cloud_context = $this->cloudContext;

    // List persistent volume for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/persistent_volume");
    $this->assertNoErrorMessage();

    // Add a new persistent volume.
    $add = $this->createPersistentVolumeTestFormData(self::K8S_PERSISTENT_VOLUME_REPEAT_COUNT);
    for ($i = 0; $i < self::K8S_PERSISTENT_VOLUME_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addPersistentVolumeMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/persistent_volume/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Persistent Volume', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/persistent_volume");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_PERSISTENT_VOLUME_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all persistent_volume listing exists.
      $this->drupalGet('/clouds/k8s/persistent_volume');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a persistent volume.
    $edit = $this->createPersistentVolumeTestFormData(self::K8S_PERSISTENT_VOLUME_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::K8S_PERSISTENT_VOLUME_REPEAT_COUNT; $i++, $num++) {

      $this->updatePersistentVolumeMockData($edit[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/persistent_volume/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Persistent Volume', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete persistent volume.
    for ($i = 0, $num = 1; $i < self::K8S_PERSISTENT_VOLUME_REPEAT_COUNT; $i++, $num++) {

      $this->deletePersistentVolumeMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/persistent_volume/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Persistent Volume', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/persistent_volume");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting persistent volume with bulk operation.
   *
   * @throws \Exception
   */
  public function testPersistentVolumeBulk(): void {

    for ($i = 0; $i < self::K8S_PERSISTENT_VOLUME_REPEAT_COUNT; $i++) {
      // Create persistent volume.
      $persistent_volumes = $this->createPersistentVolumeRandomTestFormData();
      $entities = [];
      foreach ($persistent_volumes ?: [] as $persistent_volume) {
        $entities[] = $this->createPersistentVolumeTestEntity($persistent_volume);
      }

      $this->deletePersistentVolumeMockData($persistent_volumes[0]);
      $this->runTestEntityBulk('persistent_volume', $entities);
    }
  }

}
