<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s job.
 *
 * @group K8s
 */
class K8sJobTest extends K8sTestBase {

  public const K8S_JOB_REPEAT_COUNT = 2;

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
      'list k8s job',
      'view k8s job',
      'edit k8s job',
      'add k8s job',
      'delete k8s job',
      'view k8s namespace ' . $this->namespace,
    ];
  }

  /**
   * Tests CRUD for Job.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testJob(): void {

    $cloud_context = $this->cloudContext;

    // List Job for K8s.
    $this->drupalGet("/clouds/k8s/$cloud_context/job");
    $this->assertNoErrorMessage();

    // Add a new Job.
    $add = $this->createJobTestFormData(self::K8S_JOB_REPEAT_COUNT, $this->namespace);
    for ($i = 0; $i < self::K8S_JOB_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->addJobMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/job/add",
        $add[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Job', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/job");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_JOB_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all job listing exists.
      $this->drupalGet('/clouds/k8s/job');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit a Job.
    $edit = $this->createJobTestFormData(self::K8S_JOB_REPEAT_COUNT, $this->namespace);
    for ($i = 0, $num = 1; $i < self::K8S_JOB_REPEAT_COUNT; $i++, $num++) {

      $this->updateJobMockData($edit[$i]);

      unset($edit[$i]['post_data']['namespace']);
      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/job/$num/edit",
        $edit[$i]['post_data'],
        $this->t('Save')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Job', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete Job.
    for ($i = 0, $num = 1; $i < self::K8S_JOB_REPEAT_COUNT; $i++, $num++) {

      $this->deleteJobMockData($add[$i]);

      $this->drupalPostForm(
        "/clouds/k8s/$cloud_context/job/$num/delete",
        [],
        $this->t('Delete')
      );
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Job', '@label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/k8s/$cloud_context/job");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Tests deleting jobs with bulk operation.
   *
   * @throws \Exception
   */
  public function testJobBulk(): void {

    for ($i = 0; $i < self::K8S_JOB_REPEAT_COUNT; $i++) {
      // Create Jobs.
      $jobs = $this->createJobsRandomTestFormData($this->namespace);
      $entities = [];
      foreach ($jobs ?: [] as $job) {
        $entities[] = $this->createJobTestEntity($job);
      }
      $this->deleteJobMockData($jobs[0]);
      $this->runTestEntityBulk('job', $entities);
    }
  }

}
