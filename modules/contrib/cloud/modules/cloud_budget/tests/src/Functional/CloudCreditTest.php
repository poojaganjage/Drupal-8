<?php

namespace Drupal\Tests\cloud_budget\Functional;

/**
 * Tests cloud credit.
 *
 * @group Cloud
 */
class CloudCreditTest extends CloudBudgetTestBase {

  public const CLOUD_CREDIT_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list cloud credit',
      'view cloud credit',
      'edit cloud credit',
      'add cloud credit',
      'delete cloud credit',
    ];
  }

  /**
   * Tests CRUD for Cloud Credit.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testCloudCredit(): void {

    $cloud_context = $this->cloudContext;

    // List Cloud credit for Cloud Budget.
    $this->drupalGet("/clouds/budget/$cloud_context/credit");
    $this->assertNoErrorMessage();

    // Add a new Cloud Credit.
    $add = $this->createCloudCreditTestFormData(self::CLOUD_CREDIT_REPEAT_COUNT);
    for ($i = 0; $i < self::CLOUD_CREDIT_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $label = explode(' ', $add[$i]['user[0][target_id]'])[0];
      $this->drupalPostForm(
        "/clouds/budget/$cloud_context/credit/add",
        $add[$i],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cloud Credit', '%label' => $label];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/budget/$cloud_context/credit");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($label);
    }

    // Edit a Cloud Credit.
    $edit = $this->createCloudCreditTestFormData(self::CLOUD_CREDIT_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::CLOUD_CREDIT_REPEAT_COUNT; $i++, $num++) {

      unset($edit[$i]['user[0][target_id]']);
      $this->drupalPostForm(
        "/clouds/budget/$cloud_context/credit/$num/edit",
        $edit[$i],
        $this->t('Save')
      );

      $label = explode(' ', $add[$i]['user[0][target_id]'])[0];

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cloud Credit', '%label' => $label];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Cloud Credit.
    for ($i = 0, $num = 1; $i < self::CLOUD_CREDIT_REPEAT_COUNT; $i++, $num++) {

      $label = explode(' ', $add[$i]['user[0][target_id]'])[0];

      $this->drupalPostForm(
        "/clouds/budget/$cloud_context/credit/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cloud Credit', '%label' => $label];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/budget/$cloud_context/credit");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($label);
    }
  }

  /**
   * Tests deleting cloud credits with bulk operation.
   */
  public function testCloudCreditBulk(): void {
    for ($i = 0; $i < self::CLOUD_CREDIT_REPEAT_COUNT; $i++) {
      // Create Cloud Credits.
      $cloud_credits = $this->createCloudCreditsRandomTestFormData();
      $entities = [];
      foreach ($cloud_credits ?: [] as $cloud_credit) {
        $entities[] = $this->createCloudCreditTestEntity($cloud_credit);
      }

      $this->runTestEntityBulk('credit', $entities);
    }
  }

}
