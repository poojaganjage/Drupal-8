<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s cron job.
 *
 * @group K8s
 */
class K8sCronJobTest extends K8sTestBase {

  public const K8S_CRON_JOB_REPEAT_COUNT = 2;

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
      'list k8s cron job',
      'view k8s cron job',
      'edit k8s cron job',
      'add k8s cron job',
      'delete k8s cron job',
      'view k8s namespace ' . $this->namespace,
    ];
  }

  /**
   * Tests CRUD for Cron Job.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testCronJob(): void {

    $cloud_context = $this->cloudContext;

    // List Cron Job for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/cron_job");
    $this->assertNoErrorMessage();

    // Add a new Cron Job.
    $add = $this->createCronJobTestFormData(self::K8S_CRON_JOB_REPEAT_COUNT, $this->namespace);
    for ($i = 0; $i < self::K8S_CRON_JOB_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addCronJobMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/cron_job/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cron Job', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/cron_job");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_CRON_JOB_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all cron_job listing exists.
      $this->drupalGet('/clouds/k8s/cron_job');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Cron Job.
    $edit = $this->createCronJobTestFormData(self::K8S_CRON_JOB_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_CRON_JOB_REPEAT_COUNT; $i++, $num++) {

      $this->updateCronJobMockData($edit[$i]);

      unset($edit[$i]['post_data']['namespace']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/cron_job/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cron Job', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Cron Job.
    for ($i = 0, $num = 1; $i < self::K8S_CRON_JOB_REPEAT_COUNT; $i++, $num++) {

      $this->deleteCronJobMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/cron_job/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cron Job', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/cron_job");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting cron jobs with bulk operation.
   *
   * @throws \Exception
   */
  public function testCronJobBulk(): void {

    for ($i = 0; $i < self::K8S_CRON_JOB_REPEAT_COUNT; $i++) {
      // Create Cron Jobs.
      $cron_jobs = $this->createCronJobsRandomTestFormData($this->namespace);
      $entities = [];
      foreach ($cron_jobs ?: [] as $cron_job) {
        $entities[] = $this->createCronJobTestEntity($cron_job);
      }
      $this->deleteCronJobMockData($cron_jobs[0]);
      $this->runTestEntityBulk('cron_job', $entities);
    }
  }

}
