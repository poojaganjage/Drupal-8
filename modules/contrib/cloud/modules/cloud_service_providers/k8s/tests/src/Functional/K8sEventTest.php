<?php

namespace Drupal\Tests\k8s\Functional;

/**
 * Tests K8s event.
 *
 * @group K8s
 */
class K8sEventTest extends K8sTestBase {

  public const K8S_EVENT_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list k8s event',
      'view k8s event',
    ];
  }

  /**
   * Tests CRUD for K8s event.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testEvent(): void {
    $cloud_context = $this->cloudContext;

    $data = $this->createEventTestFormData(self::K8S_EVENT_REPEAT_COUNT);
    $this->updateEventsMockData($data);

    // Update k8s events.
    $this->drupalGet("/clouds/k8s/$cloud_context/event/update");
    $this->assertNoErrorMessage();

    for ($i = 0; $i < self::K8S_EVENT_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($data[$i]['name']);
    }

    for ($i = 0, $num = 1; $i < self::K8S_EVENT_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all event listing exists.
      $this->drupalGet('/clouds/k8s/event');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($data[$j]['name']);
      }
    }
  }

}
