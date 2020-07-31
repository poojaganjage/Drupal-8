<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s storage class.
 *
 * @group K8s
 */
class K8sStorageClassTest extends K8sTestBase {

  public const K8S_STORAGE_CLASS_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list k8s storage class',
      'view k8s storage class',
      'edit k8s storage class',
      'add k8s storage class',
      'delete k8s storage class',
    ];
  }

  /**
   * Tests CRUD for Storage Class.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testStorageClass(): void {

    $cloud_context = $this->cloudContext;

    // List Storage Class for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/storage_class");
    $this->assertNoErrorMessage();

    // Add a new Storage Class.
    $add = $this->createStorageClassTestFormData(self::K8S_STORAGE_CLASS_REPEAT_COUNT);
    for ($i = 0; $i < self::K8S_STORAGE_CLASS_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addStorageClassMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/storage_class/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Storage Class', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/storage_class");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_STORAGE_CLASS_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all storage_class listing exists.
      $this->drupalGet('/clouds/k8s/storage_class');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Storage Class.
    $edit = $this->createStorageClassTestFormData(self::K8S_STORAGE_CLASS_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::K8S_STORAGE_CLASS_REPEAT_COUNT; $i++, $num++) {
      $this->updateStorageClassMockData($edit[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/storage_class/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Storage Class', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Storage Class.
    for ($i = 0, $num = 1; $i < self::K8S_STORAGE_CLASS_REPEAT_COUNT; $i++, $num++) {

      $this->deleteStorageClassMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/storage_class/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Storage Class', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/storage_class");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting storage classs with bulk operation.
   *
   * @throws \Exception
   */
  public function testStorageClassBulk(): void {

    for ($i = 0; $i < self::K8S_STORAGE_CLASS_REPEAT_COUNT; $i++) {
      // Create Storage Classs.
      $storage_classes = $this->createStorageClassesRandomTestFormData();
      $entities = [];
      foreach ($storage_classes ?: [] as $storage_class) {
        $entities[] = $this->createStorageClassTestEntity($storage_class);
      }
      $this->deleteStorageClassMockData($storage_classes[0]);
      $this->runTestEntityBulk('storage_class', $entities);
    }
  }

}
