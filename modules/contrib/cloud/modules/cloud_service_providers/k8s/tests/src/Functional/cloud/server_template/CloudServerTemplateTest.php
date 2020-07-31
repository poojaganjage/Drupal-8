<?php

namespace Drupal\Tests\k8s\Functional\cloud\server_template;

use Drupal\Tests\k8s\Functional\K8sTestBase;

/**
 * Test K8s cloud server templates (CloudServerTemplate).
 *
 * @group Cloud
 */
class CloudServerTemplateTest extends K8sTestBase {

  public const CLOUD_SERVER_TEMPLATES_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  protected function getPermissions(): array {
    // Setup namespaces.
    $namespaces = $this->createNamespacesRandomTestFormData();
    $this->addNamespaceMockData($namespaces[0]);
    $this->createNamespaceTestEntity($namespaces[0]);
    $this->namespace = $namespaces[0]['name'];
    $this->getNamespaceMockData($namespaces[0]);

    return [

      // Cloud service provider.
      "view {$this->cloudContext}",
      'edit cloud service providers',

      // Namespace.
      "view k8s namespace {$this->namespace}",

      // Launch template.
      'add cloud server templates',
      'list cloud server template',
      'view any published cloud server templates',
      'view any unpublished cloud server templates',
      'launch cloud server template',
      'edit any cloud server templates',
      'delete any cloud server templates',

      // Launch template revisions.
      'access cloud server template revisions',
      'revert all cloud server template revisions',
      'delete all cloud server template revisions',

      // Pod.
      'add k8s pod',
      'list k8s pod',
      'view any k8s pod',
      'edit any k8s pod',
      'delete any k8s pod',

      // Deployment.
      'add k8s deployment',
      'list k8s deployment',
      'view any k8s deployment',
      'edit any k8s deployment',
      'delete any k8s deployment',
    ];
  }

  /**
   * CRUD test for k8s server template.
   */
  public function testK8sServerTemplate(): void {
    $objects = $this->getK8sObjects();
    foreach ($objects ?: [] as $object) {
      try {
        $this->runServerTemplateCrudTest($object);
      }
      catch (\Exception $e) {
        throw new \RuntimeException($e->getMessage());
      }
    }
  }

  /**
   * CRUD test for k8s server template launch.
   */
  public function testK8sServerTemplateLaunch(): void {
    $objects = $this->getK8sObjects();
    foreach ($objects ?: [] as $object) {
      try {
        $this->runServerTemplateLaunchTest($object);
      }
      catch (\Exception $e) {
        throw new \RuntimeException($e->getMessage());
      }
    }
  }

  /**
   * Delete resources test for k8s server template launch.
   */
  public function testK8sServerTemplateDeleteResources(): void {
    for ($i = 0; $i < self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT; $i++) {
      try {
        $this->runServerTemplateDeleteResourcesTest();
      }
      catch (\Exception $e) {
        throw new \RuntimeException($e->getMessage());
      }
    }
  }

  /**
   * CRUD test for k8s pod server template.
   *
   * @param string $object
   *   Object to test.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  private function runServerTemplateCrudTest($object): void {

    $cloud_context = $this->cloudContext;

    // Create test.
    $pods = $this->createPodTestFormData(self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT, $this->namespace);
    $add = $this->createServerTemplateTestFormData($pods, 'yml', $object, [], self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT);
    for ($i = 0; $i < self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT; $i++) {
      // field_object is automatically calculated.
      unset($add[$i]['field_object']);
      $this->drupalPostForm(
        "/clouds/design/server_template/$cloud_context/k8s/add",
        $add[$i],
        $this->t('Save'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'launch template', '%label' => $add[$i]['name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));
    }

    // List test.
    $this->drupalGet("/clouds/design/server_template/$cloud_context");
    $this->assertNoErrorMessage();

    // Edit test.
    $pods = $this->createPodTestFormData(self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT, $this->namespace);
    $edit = $this->createServerTemplateTestFormData($pods, 'yml', $object, [], self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT);
    for ($i = 0; $i < self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT; $i++) {
      // field_object is automatically calculated.
      unset($edit[$i]['field_object']);

      // Go to listing page.
      $this->drupalGet("/clouds/design/server_template/$cloud_context");
      $this->clickLink('Edit', $i);

      $this->drupalPostForm(
        $this->getUrl(),
        $edit[$i],
        $this->t('Save')
      );

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'launch template', '%label' => $edit[$i]['name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));
    }

    // Delete test.
    for ($i = 0; $i < self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT; $i++) {

      // Go to listing page.
      $this->drupalGet("/clouds/design/server_template/$cloud_context");

      $this->clickLink('Delete', 0);
      $this->drupalPostForm(
        $this->getUrl(),
        [],
        $this->t('Delete'));
      $this->assertNoErrorMessage();
    }

    // Make sure the deleted templates are not listed anymore.
    $this->drupalGet("/clouds/design/server_template/$cloud_context");
    $this->assertNoErrorMessage();
    for ($j = 0; $j < self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT; $j++) {
      $this->assertSession()->pageTextNotContains($edit[$j]['name[0][value]']);
    }
  }

  /**
   * Launch test for k8s pod server template.
   *
   * @param string $object
   *   Object to test.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  private function runServerTemplateLaunchTest($object): void {
    $cloud_context = $this->cloudContext;

    // Create templates.
    switch ($object) {
      case 'pod':
        $data = $this->createPodTestFormData(self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT, $this->namespace);
        break;

      case 'deployment':
        $data = $this->createDeploymentTestFormData(self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT, $this->namespace);
        break;

      default:
        break;
    }

    $add = $this->createServerTemplateTestFormData($data, 'yml', $object, [], self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT);

    for ($i = 0; $i < self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT; $i++) {
      // field_object is automatically calculated.
      unset($add[$i]['field_object']);
      $this->drupalPostForm(
        "/clouds/design/server_template/$cloud_context/k8s/add",
        $add[$i],
        $this->t('Save'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'launch template', '%label' => $add[$i]['name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));
    }

    // Launch the templates.
    for ($i = 0; $i < self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT; $i++) {
      $this->drupalGet("/clouds/design/server_template/$cloud_context");
      // Update the mock data.
      switch ($object) {
        case 'pod':
          $this->getMetricsPodMockData([]);
          $this->addPodMockData($data[$i]);
          break;

        case 'deployment':
          $this->addDeploymentMockData($data[$i]);
          break;

        default;
          break;
      }

      // Navigate to the server template, and launch it.
      $this->clickLink($data[$i]['name']);
      $this->clickLink('Launch');

      $this->drupalPostForm(
        $this->getUrl(),
        [],
        $this->t('Launch')
      );

      $this->assertNoErrorMessage();
      $t_args = ['@type' => ucfirst($object), '%label' => $add[$i]['name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been launched.', $t_args)));
    }
  }

  /**
   * Delete resourceds test for k8s pod server template.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  private function runServerTemplateDeleteResourcesTest(): void {
    $cloud_context = $this->cloudContext;
    $available_resources = k8s_supported_cloud_server_templates();

    $keys = array_rand($available_resources, random_int(1, count($available_resources)));
    if (!is_array($keys)) {
      $keys = [$keys];
    }
    $keys = array_flip($keys);
    $objects = array_intersect_key($available_resources, $keys);

    // Create resources.
    $entities = [];
    foreach ($objects as $object => $name) {
      $count = random_int(1, self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT);
      $createFormDataFunction = "create{$name}TestFormData";
      if (!method_exists($this, $createFormDataFunction)) {
        continue;
      }

      $items = $this->$createFormDataFunction($count, $this->namespace);
      if ($object === 'pod') {
        $this->getMetricsPodMockData([]);
      }

      $addMockDataFunction = "add{$name}MockData";
      $createTestEntityFunction = "create{$name}TestEntity";
      $deleteMockDataFunction = "delete{$name}MockData";

      if (!method_exists($this, $addMockDataFunction)) {
        continue;
      }

      foreach ($items ?: [] as $item) {
        $this->$addMockDataFunction($item);
        $entities[] = $this->$createTestEntityFunction($item);
        $this->$deleteMockDataFunction($item);
      }
    }

    $adds = $this->createServerTemplateTestFormData([], 'git', $object, $entities);
    $adds[0]['name'] = $adds[0]['name[0][value]'];
    $adds[0]['cloud_context'] = $adds[0]['cloud_context[0][value]'];
    $server_template = $this->createServerTemplateTestEntity($adds[0]);
    $id = $server_template->id();
    $this->drupalGet("/clouds/design/server_template/$cloud_context/$id/delete");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains(
      $this->t("Please make sure the following resources will be deleted if you select 'Delete both application and resources' or 'Delete only resources' option."));
    $this->assertSession()->fieldEnabled('field_delete_option');

    foreach ($entities ?: [] as $entity) {
      $this->assertSession()->pageTextContains(
        $this->t('@type: @name', [
          '@type' => $entity->getEntityType()->getSingularLabel(),
          '@name' => $entity->getName(),
          ':url' => $entity->toUrl('canonical')->toString(),
        ])
      );
    }
    $options = ['both', 'resources', 'application'];
    $option = $options[array_rand($options)];

    $this->drupalPostForm(
      "/clouds/design/server_template/$cloud_context/$id/delete",
      ['field_delete_option' => $option],
      $this->t('Delete'));

    $this->assertNoErrorMessage();

    if ($option === 'both' || $option === 'resources') {
      foreach ($entities ?: [] as $entity) {
        $t_args = [
          '@type' => $entity->getEntityType()->getSingularLabel(),
          '@label' => $entity->label(),
          '@cloud_context' => $cloud_context,
        ];
        $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label on @cloud_context has been deleted.', $t_args)));
      }
    }

    if ($option === 'both' || $option === 'application') {

      $t_args = [
        '@type'  => $server_template->getEntityType()->getSingularLabel(),
        '@label' => $server_template->label(),
      ];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));
    }
  }

  /**
   * Get K8bs objects to test.
   *
   * @return array
   *   Array of K8s objects.
   */
  protected function getK8sObjects(): array {
    return [
      'pod',
      'deployment',
    ];
  }

}
