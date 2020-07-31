<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s persistent volume claim.
 *
 * @group K8s
 */
class K8sPersistentVolumeClaimTest extends K8sTestBase {

  public const K8S_PERSISTENT_VOLUME_CLAIM_REPEAT_COUNT = 2;

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
      'list k8s persistent volume claim',
      'add k8s persistent volume claim',
      'edit k8s persistent volume claim',
      'delete k8s persistent volume claim',
      'view k8s namespace ' . $this->namespace,
    ];
  }

  /**
   * Tests CRUD for Persistent Volume Claim.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testPersistentVolumeClaim(): void {

    $cloud_context = $this->cloudContext;

    // List Persistent Volume Claim for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/persistent_volume_claim");
    $this->assertNoErrorMessage();

    // Add a new Persistent Volume Claim.
    $add = $this->createPersistentVolumeClaimTestFormData(self::K8S_PERSISTENT_VOLUME_CLAIM_REPEAT_COUNT, $this->namespace);
    for ($i = 0; $i < self::K8S_PERSISTENT_VOLUME_CLAIM_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addPersistentVolumeClaimMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/persistent_volume_claim/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Persistent Volume Claim', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/persistent_volume_claim");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_PERSISTENT_VOLUME_CLAIM_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all persistent_volume_claim listing exists.
      $this->drupalGet('/clouds/k8s/persistent_volume_claim');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Persistent Volume Claim.
    $edit = $this->createPersistentVolumeClaimTestFormData(self::K8S_PERSISTENT_VOLUME_CLAIM_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_PERSISTENT_VOLUME_CLAIM_REPEAT_COUNT; $i++, $num++) {

      $this->updatePersistentVolumeClaimMockData($edit[$i]);

      unset($edit[$i]['post_data']['namespace']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/persistent_volume_claim/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Persistent Volume Claim', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Persistent Volume Claim.
    for ($i = 0, $num = 1; $i < self::K8S_PERSISTENT_VOLUME_CLAIM_REPEAT_COUNT; $i++, $num++) {

      $this->deletePersistentVolumeClaimMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/persistent_volume_claim/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Persistent Volume Claim', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/persistent_volume_claim");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting persistent volume claim with bulk operation.
   *
   * @throws \Exception
   */
  public function testPersistentVolumeClaimBulk(): void {

    for ($i = 0; $i < self::K8S_PERSISTENT_VOLUME_CLAIM_REPEAT_COUNT; $i++) {
      // Create Persistent Volume Claim.
      $persistent_volume_claims = $this->createPersistentVolumeClaimsRandomTestFormData();
      $entities = [];
      foreach ($persistent_volume_claims ?: [] as $persistent_volume_claim) {
        $entities[] = $this->createPersistentVolumeClaimTestEntity($persistent_volume_claim);
      }
      $this->deletePersistentVolumeClaimMockData($persistent_volume_claims[0]);
      $this->runTestEntityBulk('persistent_volume_claim', $entities);
    }
  }

}
