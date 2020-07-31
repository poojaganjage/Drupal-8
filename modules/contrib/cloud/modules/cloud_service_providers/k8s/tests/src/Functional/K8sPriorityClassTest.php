<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s priority class.
 *
 * @group K8s
 */
class K8sPriorityClassTest extends K8sTestBase {

  public const K8S_PRIORITY_CLASS_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  protected function getPermissions(): array {
    return [
      'list k8s priority class',
      'add k8s priority class',
      'edit k8s priority class',
      'delete k8s priority class',
      'view k8s priority class',
    ];
  }

  /**
   * Tests CRUD for Priority Class.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testPriorityClass(): void {

    $cloud_context = $this->cloudContext;

    // List Priority Class for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/priority_class");
    $this->assertNoErrorMessage();

    // Add a new Priority Class.
    $add = $this->createPriorityClassTestFormData(self::K8S_PRIORITY_CLASS_REPEAT_COUNT);
    for ($i = 0; $i < self::K8S_PRIORITY_CLASS_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addPriorityClassMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/priority_class/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Priority Class', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/priority_class");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_PRIORITY_CLASS_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all priority_class listing exists.
      $this->drupalGet('/clouds/k8s/priority_class');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit the description of a Priority Class.
    // k8sAPI v1 returns 422 Unprocessable Entity when trying to edit the value.
    $edit = $this->createPriorityClassTestFormData(self::K8S_PRIORITY_CLASS_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::K8S_PRIORITY_CLASS_REPEAT_COUNT; $i++, $num++) {

      $this->updatePriorityClassMockData($edit[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/priority_class/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Priority Class', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Priority Class.
    for ($i = 0, $num = 1; $i < self::K8S_PRIORITY_CLASS_REPEAT_COUNT; $i++, $num++) {

      $this->deletePriorityClassMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/priority_class/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Priority Class', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/priority_class");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting Priority Classes with bulk operation.
   *
   * @throws \Exception
   */
  public function testPriorityClassBulk(): void {

    for ($i = 0; $i < self::K8S_PRIORITY_CLASS_REPEAT_COUNT; $i++) {
      // Create Priority Classes.
      $priority_classes = $this->createPriorityClassesRandomTestFormData();
      $entities = [];
      foreach ($priority_classes ?: [] as $priority_class) {
        $entities[] = $this->createPriorityClassTestEntity($priority_class);
      }
      $this->deletePriorityClassMockData($priority_classes[0]);
      $this->runTestEntityBulk('priority_class', $entities);
    }
  }

}
