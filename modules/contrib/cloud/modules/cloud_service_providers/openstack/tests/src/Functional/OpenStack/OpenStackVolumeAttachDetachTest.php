<?php

namespace Drupal\Tests\openstack\Functional\OpenStack;

use Drupal\openstack\Entity\OpenStackInstance;
use Drupal\openstack\Entity\OpenStackVolume;
use Drupal\Tests\openstack\Functional\OpenStackTestBase;

/**
 * Tests OpenStack Volume for attach and detach operations.
 *
 * @group OpenStack
 */
class OpenStackVolumeAttachDetachTest extends OpenStackTestBase {

  /**
   * Number of times to repeat the test.
   */
  public const MAX_TEST_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list openstack volume',
      'add openstack volume',
      'view any openstack volume',
      'edit any openstack volume',
      'delete any openstack volume',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getMockDataTemplateVars(): array {
    return [
      'create_time' => date('c'),
    ];
  }

  /**
   * Test volume attach.
   */
  public function testVolumeAttachDetach(): void {
    try {
      $this->repeatTestVolumeAttachDetach(self::MAX_TEST_REPEAT_COUNT);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

  /**
   * Repeating test volume attach detach.
   *
   * @param int $max_test_repeat_count
   *   Max test repeating count.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  private function repeatTestVolumeAttachDetach($max_test_repeat_count = 1): void {

    $regions = ['RegionOne'];
    for ($i = 1; $i <= $max_test_repeat_count; $i++) {
      // Setup for testing.
      $device_name = $this->random->name(8, TRUE);

      // Setup a test instance.
      $instance = $this->createInstanceTestEntity(OpenStackInstance::class, $i, $regions);
      $instance_id = $instance->getInstanceId();

      // Setup a test volume.
      $volume = $this->createVolumeTestEntity(
        OpenStackVolume::class,
        $i,
        'vol-' . $this->getRandomId(),
        "volume-name #$i - {$this->random->name(32, TRUE)}",
        $this->cloudContext,
        $this->loggedInUser->id()
      );
      $volume_id = $volume->getVolumeId();

      $attach_data = [
        'device_name' => $device_name,
        'instance_id' => $instance_id,
      ];

      // Test attach.
      $this->updateAttachDetachVolumeMockData('AttachVolume', $device_name, $volume_id, $instance_id, 'attaching');
      $this->drupalPostForm("/clouds/openstack/$this->cloudContext/volume/$i/attach",
        $attach_data,
        $this->t('Attach'));

      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($this->t('The volume @volume is attaching to @instance', [
        '@instance' => $instance_id,
        '@volume' => $volume_id,
      ]));

      // Test detach.
      $volume->setState('in-use');
      $volume->setAttachmentInformation($instance_id);
      $volume->save();
      $this->updateAttachDetachVolumeMockData('DetachVolume', $device_name, $volume_id, $instance_id, 'detaching');
      $this->drupalPostForm("/clouds/openstack/$this->cloudContext/volume/$i/detach",
        [],
        $this->t('Detach'));
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($this->t('The volume @volume is detaching from @instance', [
        '@instance' => $instance_id,
        '@volume' => $volume_id,
      ]));
    }

  }

  /**
   * Tests detaching Volumes with bulk operation.
   *
   * @throws \Exception
   */
  public function testVolumeBulkDetach(): void {

    $cloud_context = $this->cloudContext;
    $total_count = 0;
    $total_volumes = [];
    $regions = ['RegionOne'];

    // NOTE: $num needs to be incremented outside $j loop.
    for ($i = 0, $num = 1; $i < self::MAX_TEST_REPEAT_COUNT; $i++) {

      $volumes_count = random_int(1, self::MAX_TEST_REPEAT_COUNT);
      // Create Volumes.
      $volumes = $this->createVolumeTestFormData($volumes_count, TRUE);

      for ($j = 0; $j < $volumes_count; $j++, $num++) {
        // Setup for testing.
        $device_name = $this->random->name(8, TRUE);

        // Setup a test instance.
        $instance = $this->createInstanceTestEntity(OpenStackInstance::class, $j, $regions);
        $instance_id = $instance->getInstanceId();
        $this->addInstanceMockData(OpenStackInstanceTest::class, $instance->getName(), $instance->getKeyPairName(), $regions);

        // Setup a test volume.
        $volume = $this->createVolumeTestEntity(
          OpenStackVolume::class,
          $j,
          $volumes[$j]['name'],
          $volumes[$j]['name'],
          $this->cloudContext,
          $this->loggedInUser->id()
        );
        $volume_id = $volume->getVolumeId();
        $this->addVolumeMockData($volumes[$j], OpenStackVolume::TAG_CREATED_BY_UID);

        $attach_data = [
          'device_name' => $device_name,
          'instance_id' => $instance_id,
        ];
        $this->drupalGet("/clouds/openstack/$cloud_context/volume");

        // Test attach.
        $this->updateAttachDetachVolumeMockData('AttachVolume', $device_name, $volume_id, $instance_id, 'attaching');
        $this->drupalPostForm("/clouds/openstack/$this->cloudContext/volume/$num/attach",
          $attach_data,
          $this->t('Attach'));

        $volume->setState('in-use');
        $volume->setAttachmentInformation($instance_id);
        $volume->save();
        $volumes[$j]['instance_id'] = $instance_id;

      }

      $total_count += $volumes_count;
      $total_volumes = array_merge($total_volumes, $volumes);

      $this->drupalGet("/clouds/openstack/$cloud_context/volume");

      $data = [];
      $data['action'] = 'openstack_volume_detach_action';

      $checkboxes = $this->cssSelect('input[type=checkbox]');
      foreach ($checkboxes ?: [] as $checkbox) {
        if ($checkbox->getAttribute('name') === NULL) {
          continue;
        }

        $data[$checkbox->getAttribute('name')] = $checkbox->getAttribute('value');
      }

      // Confirm.
      $this->drupalPostForm(
        "/clouds/openstack/$cloud_context/volume",
        $data,
        $this->t('Apply to selected items')
      );
      $this->assertNoErrorMessage();

      $message = 'Are you sure you want to detach these Volumes?';

      if ($total_count === 1) {
        $message = 'Are you sure you want to detach this Volume?';
      }
      $this->assertSession()->pageTextContains($message);

      foreach ($total_volumes ?: [] as $volume) {
        $this->assertSession()->pageTextContains($volume['name']);
      }

      // Disassociate.
      $this->drupalPostForm(
        "/clouds/openstack/$cloud_context/volume/detach_multiple",
        [],
        $this->t('Detach')
      );

      $this->assertNoErrorMessage();
      if ($total_count === 1) {
        $this->assertSession()->pageTextContains("Detached $volumes_count Volume.");
      }
      else {
        $this->assertSession()->pageTextContains("Detached $total_count Volumes.");
      }

      for ($j = 0; $j < $total_count; $j++) {
        $volume = $total_volumes[$j];
        $t_args = ['@type' => 'Volume', '%label' => $volume['name']];
        $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been detached.', $t_args)));
        $this->updateVolumeMockData($j, $volume['name'], NULL);
      }

      // Click 'Refresh'.
      $this->clickLink($this->t('Refresh'));
      $this->assertSession()->pageTextContains($this->t('Updated Volumes.'));

      // Make sure if disassociated from an instance.
      foreach ($total_volumes ?: [] as $volume) {
        $this->assertSession()->pageTextNotContains($volume['instance_id']);
      }
    }
  }

}
