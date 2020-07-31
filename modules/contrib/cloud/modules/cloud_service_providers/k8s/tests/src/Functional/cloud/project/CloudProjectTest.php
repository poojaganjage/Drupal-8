<?php

namespace Drupal\Tests\k8s\Functional\cloud\project;

use Drupal\Tests\k8s\Functional\K8sTestBase;

/**
 * Test K8s cloud project (CloudProject).
 *
 * @group Cloud
 */
class CloudProjectTest extends K8sTestBase {

  public const CLOUD_PROJECTS_REPEAT_COUNT = 3;

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  protected function getPermissions():array {
    return [
      'list k8s namespace',
      'view k8s namespace',
      'edit k8s namespace',
      'add k8s namespace',
      'delete k8s namespace',
      'list k8s resource quota',
      'view k8s resource quota',
      'edit k8s resource quota',
      'add k8s resource quota',
      'delete k8s resource quota',
      'launch cloud project',
      'add cloud projects',
      'list cloud project',
      'delete any cloud projects',
      'edit any cloud projects',
      'view any published cloud projects',
      'view any unpublished cloud projects',
      'access cloud project revisions',
      'revert all cloud project revisions',
      'delete all cloud project revisions',
    ];
  }

  /**
   * CRUD test for k8s project test.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testK8sProjectCrudTest(): void {

    $cloud_context = $this->cloudContext;

    // Create a new project.
    $add = $this->createProjectTestFormData(self::CLOUD_PROJECTS_REPEAT_COUNT);
    $edit = $this->createProjectTestFormData(self::CLOUD_PROJECTS_REPEAT_COUNT);
    for ($i = 0; $i < self::CLOUD_PROJECTS_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      // Create project.
      $this->drupalPostForm(
        "/clouds/design/project/$cloud_context/k8s/add",
        $add[$i],
        $this->t('Save'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cloud Project', '%label' => $add[$i]['name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // List test.
      $this->drupalGet("/clouds/design/project/$cloud_context");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name[0][value]']);

      // Edit test.
      unset(
        $edit[$i]['name[0][value]'],
        $edit[$i]['field_username']
      );
      $this->clickLink($add[$i]['name[0][value]']);
      $this->clickLink('Edit', 0);
      $this->drupalPostForm(
        $this->getUrl(),
        $edit[$i],
        $this->t('Save')
      );

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cloud Project', '%label' => $add[$i]['name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      // Click 'Refresh'.
      // @TODO: Need tests for the entities from the mock objects.
      $this->clickLink($this->t('Refresh'));
      $this->assertSession()->pageTextContains($this->t('Unnecessary to update cloud projects.'));
      $this->assertNoErrorMessage();

      // Copy test.
      $copy[$i] = $edit[$i];
      $copy[$i]['name[0][value]'] = $add[$i]['name[0][value]'] . '-copy';
      $copy[$i]['field_username'] = $add[$i]['field_username'];

      // Go to listing page.
      $this->drupalGet("/clouds/design/project/$cloud_context");
      $this->clickLink('Copy', 0);

      $this->drupalPostForm(
        $this->getUrl(),
        $copy[$i],
        $this->t('Copy')
      );

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cloud Project', '%label' => $copy[$i]['name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Go to listing page.
      $this->drupalGet("/clouds/design/project/$cloud_context");
      $this->assertSession()->pageTextContains($add[$i]['name[0][value]']);
      $this->assertSession()->pageTextContains($copy[$i]['name[0][value]']);

      // Delete test.
      $this->clickLink($add[$i]['name[0][value]']);
      $this->clickLink('Delete', 0);
      $this->drupalPostForm(
        $this->getUrl(),
        [],
        $this->t('Delete'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cloud Project', '@label' => $add[$i]['name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Go to listing page.
      $this->drupalGet("/clouds/design/project/$cloud_context");
      $this->clickLink($copy[$i]['name[0][value]']);
      $this->clickLink('Delete', 0);
      $this->drupalPostForm(
        $this->getUrl(),
        [],
        $this->t('Delete')
      );

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cloud Project', '@label' => $copy[$i]['name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure the deleted projects are not listed any more.
      $this->drupalGet("/clouds/design/project/$cloud_context");
      $this->assertSession()->pageTextNotContains($add[$i]['name[0][value]']);
      $this->assertSession()->pageTextNotContains($copy[$i]['name[0][value]']);
    }
  }

  /**
   * Tests launch a project.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testLaunchProject(): void {

    $cloud_context = $this->cloudContext;

    // List Namespace for K8s.
    $this->drupalGet("/clouds/design/project/$cloud_context");
    $this->assertNoErrorMessage();

    // Create a new project.
    $add = $this->createProjectTestFormData(self::CLOUD_PROJECTS_REPEAT_COUNT);
    $edit = $this->createProjectTestFormData(self::CLOUD_PROJECTS_REPEAT_COUNT);
    for ($i = 0; $i < self::CLOUD_PROJECTS_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      // Make sure resource quota is created.
      $add[$i]['field_enable_resource_scheduler[value]'] = 1;

      // Create project.
      $this->drupalPostForm(
        "/clouds/design/project/$cloud_context/k8s/add",
        $add[$i],
        $this->t('Save')
      );

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cloud Project', '%label' => $add[$i]['name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      $this->drupalGet("/clouds/design/project/$cloud_context");
      // Create data of Namespace and Resource quota.
      $namespace = $this->arrangeDataForNamespace($add[$i]);
      $this->addNamespaceMockData($namespace);
      $this->createNamespaceTestEntity($namespace);
      $resource_quota = $this->arrangeDataForResourceQuota($add[$i]);
      $this->addResourceQuotaMockData($resource_quota);

      // Launch after Editing project.
      $edit[$i]['name[0][value]'] = $add[$i]['name[0][value]'];
      $namespace_edit = $this->arrangeDataForNamespace($edit[$i]);
      $this->updateNamespaceMockData($namespace_edit);
      if (!empty($edit[$i]['field_enable_resource_scheduler[value]'])) {
        $resource_quota_edit = $this->arrangeDataForResourceQuota($edit[$i]);
        if (!empty($add[$i]['field_enable_resource_scheduler[value]'])) {
          $this->updateResourceQuotaMockData($resource_quota_edit);
        }
        else {
          $this->addResourceQuotaMockData($resource_quota_edit);
        }
      }
      else {
        if (!empty($add[$i]['field_enable_resource_scheduler[value]'])) {
          $this->deleteResourceQuotaMockData($resource_quota);
        }
      }

      unset(
        $edit[$i]['name[0][value]'],
        $edit[$i]['field_username']
      );
      $this->drupalGet("/clouds/design/project/$cloud_context");
      $this->clickLink($namespace_edit['name']);
      $this->clickLink('Edit', 0);
      $this->drupalPostForm(
        $this->getUrl(),
        $edit[$i],
        $this->t('Save')
      );

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cloud Project', '%label' => $add[$i]['name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      $this->drupalGet("/clouds/design/project/$cloud_context");
      $this->clickLink($namespace_edit['name']);
      $this->clickLink('Launch');
      $this->drupalPostForm(
        $this->getUrl(),
        [],
        $this->t('Launch')
      );

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Cloud Project', '%label' => $namespace_edit['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been launched.', $t_args)));

      $t_args = [
        '@type' => 'Namespace',
        '%label' => $namespace_edit['name'],
        '@cloud_context' => $cloud_context,
      ];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label on @cloud_context has been updated.', $t_args)));

      if (!empty($edit[$i]['field_enable_resource_scheduler[value]'])) {
        if (!empty($add[$i]['field_enable_resource_scheduler[value]'])) {
          // Initial field_enable_resource_scheduler: on.
          // Updating field_enable_resource_scheduler: on.
          // Expected result: updated.
          $t_args = [
            '@type' => 'Resource Quota',
            '%label' => $namespace_edit['name'],
            '@cloud_context' => $cloud_context,
            '@passive_operation' => 'updated',
          ];
        }
        else {
          // Initial field_enable_resource_scheduler: off.
          // Updating field_enable_resource_scheduler: on.
          // Expected result: created.
          $t_args = [
            '@type' => 'Resource Quota',
            '%label' => $namespace_edit['name'],
            '@cloud_context' => $cloud_context,
            '@passive_operation' => 'created',
          ];
        }
        $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label on @cloud_context has been @passive_operation.', $t_args)));
      }
      elseif (!empty($add[$i]['field_enable_resource_scheduler[value]'])) {
        // Initial field_enable_resource_scheduler: on.
        // Updating field_enable_resource_scheduler: off.
        // Expected result: deleted.
        $t_args = [
          '@type' => 'Resource Quota',
          '@label' => $namespace['name'],
          '@cloud_context' => $cloud_context,
          '@passive_operation' => 'deleted',
        ];
        $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label on @cloud_context has been @passive_operation.', $t_args)));
      }

      // Delete test.
      if (!empty($edit[$i]['field_enable_resource_scheduler[value]'])) {
        $this->deleteResourceQuotaMockData($resource_quota_edit);
      }
      else {
        if (!empty($add[$i]['field_enable_resource_scheduler[value]'])) {
          $this->deleteResourceQuotaMockData($resource_quota);
        }
      }
      $this->deleteNamespaceMockData($namespace_edit);

      $this->drupalGet("/clouds/design/project/$cloud_context");
      $this->clickLink($namespace_edit['name']);
      $this->clickLink('Delete', 0);
      $this->drupalPostForm(
        $this->getUrl(),
        [],
        $this->t('Delete')
      );

      $t_args = ['@type' => 'Cloud Project', '@label' => $namespace_edit['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      $t_args = [
        '@type' => 'Namespace',
        '@label' => $namespace_edit['name'],
        '@cloud_context' => $cloud_context,
      ];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label on @cloud_context has been deleted.', $t_args)));

      $t_args = ['@type' => 'Role', '@label' => $namespace_edit['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Check the result.
      $this->drupalGet("/clouds/k8s/$cloud_context/resource_quota");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextNotContains($namespace_edit['name']);

      $this->drupalGet("/clouds/k8s/$cloud_context/namespace");
      $this->assertNoErrorMessage();
    }
  }

  /**
   * Test k8s time scheduler.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  public function testTimeScheduler(): void {
    $cloud_context = $this->cloudContext;
    $project = $this->createProjectTestFormData(self::CLOUD_PROJECTS_REPEAT_COUNT);
    $k8s_service = \Drupal::service('k8s');
    for ($i = 0; $i < self::CLOUD_PROJECTS_REPEAT_COUNT; $i++) {

      // Change the parameters.
      $project[$i]['field_enable_time_scheduler[value]'] = 1;
      $project[$i]['field_enable_resource_scheduler[value]'] = 1;
      $project[$i]['cloud_context'] = $cloud_context;
      $cloud_project = $this->arrangeDataForCloudProject($project[$i]);
      $resource_quota = $this->arrangeDataForResourceQuota($project[$i]);

      // Create the necessary entities.
      // Premise that no resource quota is prepared at the beginning.
      $this->createProjectTestEntity($cloud_project);
      $this->createNamespaceTestEntity($resource_quota);
      $this->namespace = $resource_quota['name'];

      // Extract the time range information from Project entity.
      $startup_time = "{$project[$i]['field_startup_time_hour']}:{$project[$i]['field_startup_time_minute']}";
      $stop_time = "{$project[$i]['field_stop_time_hour']}:{$project[$i]['field_stop_time_minute']}";

      // Check the the current time only, not the changed time because it can be
      // very rare that current time and changed time lay in different time
      // range.  Therefore, this test validates only three parameters (cpu,
      // memory and pods) changed by the result passed through
      // validateScheduledTime function.
      $is_validated = $k8s_service->validateScheduledTime($startup_time, $stop_time);
      if (!$is_validated) {
        $resource_quota['spec']['hard']['cpu'] = '0m';
        $resource_quota['spec']['hard']['memory'] = '0Mi';
        $resource_quota['spec']['hard']['pods'] = 0;
      }

      // Change a mock data.
      $this->addResourceQuotaMockData($resource_quota);

      $k8s_service->runTimeScheduler();

      // Create an entity.
      $this->createResourceQuotaTestEntity($resource_quota);

      // Check the result.
      $this->drupalGet("/clouds/design/project/$cloud_context");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContainsOnce($resource_quota['name']);

      $this->drupalGet("/clouds/k8s/$cloud_context/namespace");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContainsOnce($resource_quota['name']);

      $this->drupalGet("/clouds/k8s/$cloud_context/resource_quota");
      $this->assertNoErrorMessage();

      // @TODO Check the reason why the following entity does not appear.
      // $this->assertSession()->pageTextContainsOnce($resource_quota['name']);
      $result = $k8s_service->getResourceQuotas();
      $this->assertEquals($result[$i]['status']['hard']['cpu'], $resource_quota['spec']['hard']['cpu']);
      $this->assertEquals($result[$i]['status']['hard']['memory'], $resource_quota['spec']['hard']['memory']);
      $this->assertEquals($result[$i]['status']['hard']['pods'], $resource_quota['spec']['hard']['pods']);
    }
  }

  /**
   * The arrangement of data for resource quota from test data.
   *
   * @param array $data
   *   Test data for namespace.
   *
   * @return array
   *   Arrangement of test data.
   */
  private function arrangeDataForNamespace(array $data): array {
    $namespace = [];
    $namespace['name'] = $data['name[0][value]'];
    if (!empty($data['field_enable_time_scheduler[value]'])) {
      $namespace['metadata']['annotations']['startup_time'] = $data['field_startup_time_hour'] . ':' . $data['field_startup_time_minute'];
      $namespace['metadata']['annotations']['stop_time'] = $data['field_stop_time_hour'] . ':' . $data['field_stop_time_minute'];
    }
    if (!empty($data['field_enable_resource_scheduler[value]'])) {
      $namespace['metadata']['annotations']['request_cpu'] = $data['field_request_cpu[0][value]'] . 'm';
      $namespace['metadata']['annotations']['request_memory'] = $data['field_request_memory[0][value]'] . 'Mi';
      $namespace['metadata']['annotations']['pod_count'] = $data['field_pod_count[0][value]'];
    }
    return $namespace;
  }

  /**
   * The arrangement of data for resource quota from test data.
   *
   * @param array $data
   *   Test data for resource quota.
   *
   * @return array
   *   Arrangement of test data.
   */
  private function arrangeDataForCloudProject(array $data): array {
    $project = [
      'post_data' => [
        'namespace' => $data['name[0][value]'],
      ],
      'name' => $data['name[0][value]'],
      'cloud_context' => $data['cloud_context'],
      'field_request_cpu' => $data['field_request_cpu[0][value]'] . 'm',
      'field_request_memory' => $data['field_request_memory[0][value]'] . 'Mi',
      'field_request_pods' => $data['field_pod_count[0][value]'],
      'field_enable_resource_scheduler' => $data['field_enable_resource_scheduler[value]'],
      'field_enable_time_scheduler' => $data['field_enable_time_scheduler[value]'],
      'field_startup_time_hour' => $data['field_startup_time_hour'],
      'field_startup_time_minute' => $data['field_startup_time_minute'],
      'field_stop_time_hour' => $data['field_stop_time_hour'],
      'field_stop_time_minute' => $data['field_stop_time_minute'],
    ];

    return $project;
  }

  /**
   * The arrangement of data for resource quota from test data.
   *
   * @param array $data
   *   Test data for resource quota.
   *
   * @return array
   *   Arrangement of test data.
   */
  private function arrangeDataForResourceQuota(array $data): array {
    $resource_quota = [
      'post_data' => [
        'namespace' => $data['name[0][value]'],
      ],
      'name' => $data['name[0][value]'],
      'spec' => [
        'hard' => [
          'cpu' => $data['field_request_cpu[0][value]'] . 'm',
          'memory' => $data['field_request_memory[0][value]'] . 'Mi',
          'pods' => $data['field_pod_count[0][value]'],
        ],
      ],
    ];

    return $resource_quota;
  }

}
