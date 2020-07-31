<?php

namespace Drupal\Tests\openstack\Functional\OpenStack;

use Drupal\openstack\Entity\OpenStackInstance;
use Drupal\openstack\Entity\OpenStackSnapshot;
use Drupal\openstack\Entity\OpenStackVolume;
use Drupal\Tests\openstack\Functional\OpenStackTestBase;
use Drupal\Tests\cloud\Functional\Utils;

/**
 * Tests OpenStack Volume.
 *
 * @group OpenStack
 */
class OpenStackVolumeTest extends OpenStackTestBase {

  public const OPENSTACK_VOLUME_REPEAT_COUNT = 2;

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

      'add openstack snapshot',
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  protected function getMockDataTemplateVars(): array {
    return [
      'volume_id' => 'vol-' . $this->getRandomId(),
      'create_time' => date('c'),
      'uid' => Utils::getRandomUid(),
    ];
  }

  /**
   * Tests CRUD for Volume information.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Exception
   */
  public function testVolume(): void {
    $cloud_context = $this->cloudContext;

    // List Volume for Amazon OpenStack.
    $this->drupalGet("/clouds/openstack/$cloud_context/volume");
    $this->assertNoErrorMessage();

    // Add a new Volume.
    $delete_count = 0;
    $add = $this->createVolumeTestFormData(self::OPENSTACK_VOLUME_REPEAT_COUNT, TRUE);
    for ($i = 0, $num = 1; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++, $num++) {
      $this->reloadMockData();

      $state = $this->createRandomState();
      $volume_id = 'vol-' . $this->getRandomId();
      $snapshot_name = 'snapshot-name' . $this->random->name(10, TRUE);
      $this->updateCreateVolumeMockData($state, $volume_id);
      $this->createSnapshotTestEntity(OpenStackSnapshot::class, $i, $add[$i]['snapshot_id'], $snapshot_name, $cloud_context);
      $this->updateDescribeSnapshotsMockData([['id' => $add[$i]['snapshot_id'], 'name' => $snapshot_name]]);
      if ($state !== 'in-use') {
        $delete_count++;
      }

      $this->drupalPostForm("/clouds/openstack/$cloud_context/volume/add",
                            $add[$i],
                            $this->t('Save'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Volume', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/openstack/$cloud_context/volume");
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$i]['name']);
      }

      // Assert delete link count.
      if ($delete_count > 0) {
        $this->assertSession()->linkExists($this->t('Delete'), $delete_count - 1);
      }

      // Make sure view.
      $this->drupalGet("/clouds/openstack/$cloud_context/volume/$num");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($volume_id);
      $this->assertSession()->pageTextContains($add[$i]['snapshot_id']);
      $this->assertSession()->pageTextContains($snapshot_name);
    }

    for ($i = 0, $num = 1; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all volume listing exists.
      $this->drupalGet('/clouds/openstack/volume');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit an Volume information.
    $edit = $this->createVolumeTestFormData(self::OPENSTACK_VOLUME_REPEAT_COUNT, TRUE);
    for ($i = 0, $num = 1; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++, $num++) {

      unset(
        $edit[$i]['snapshot_id'],
        $edit[$i]['size'],
        $edit[$i]['availability_zone'],
        $edit[$i]['volume_type']
      );

      $this->drupalPostForm("/clouds/openstack/$cloud_context/volume/$num/edit",
                            $edit[$i],
                            $this->t('Save'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Volume', '%label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/openstack/$cloud_context/volume");
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($edit[$i]['name']);
      }
    }

    // Delete Volume.
    for ($i = 0, $num = 1; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++, $num++) {

      $this->drupalGet("/clouds/openstack/$cloud_context/volume/$num/delete");
      $this->assertNoErrorMessage();
      $this->drupalPostForm("/clouds/openstack/$cloud_context/volume/$num/delete",
                            [],
                            $this->t('Delete'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Volume', '@label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/openstack/$cloud_context/volume");
      $this->assertNoErrorMessage();
    }
  }

  /**
   * Test updating volume list.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Exception
   */
  public function testUpdateVolumeList(): void {

    $cloud_context = $this->cloudContext;

    // Add a new Volume.
    $add = $this->createVolumeTestFormData(self::OPENSTACK_VOLUME_REPEAT_COUNT, TRUE);
    for ($i = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {
      $this->addVolumeMockData($add[$i], OpenStackVolume::TAG_CREATED_BY_UID);
      $snapshot_name = 'snapshot-name' . $this->random->name(10, TRUE);
      $this->createSnapshotTestEntity(OpenStackSnapshot::class, $i, $add[$i]['snapshot_id'], $snapshot_name, $cloud_context);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/volume");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Volumes.'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Make sure detailed and edit view.
    for ($i = 0, $num = 1; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++, $num++) {

      $this->drupalGet("/clouds/openstack/$cloud_context/volume/$num");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/volume/$num/edit");
      $this->assertSession()->linkExists($this->t('Attach'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/volume/$num/attach");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/volume/$num/delete");
      $this->assertSession()->linkExists($this->t('List OpenStack Volumes'));
      // Click 'Refresh'.
      $this->clickLink($this->t('List OpenStack Volumes'));
      $this->assertNoErrorMessage();

      $this->drupalGet("/clouds/openstack/$cloud_context/volume/$num/edit");
      $this->assertSession()->linkExists('Edit');
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/volume/$num/edit");
      $this->assertSession()->linkExists($this->t('Attach'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/volume/$num/attach");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/volume/$num/delete");
    }

    $regions = ['RegionOne'];
    // Edit Volume information.
    for ($i = 0, $num = 1; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++, $num++) {

      // Setup a test instance.
      $instance = $this->createInstanceTestEntity(OpenStackInstance::class, $i, $regions);
      $instance_id = $instance->getInstanceId();

      // Change Volume Name in mock data.
      $add[$i]['name'] = "volume-name #$num - {$this->random->name(32, TRUE)}";

      $this->updateVolumeMockData($i, $add[$i]['name'], $instance_id);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/volume");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Volumes.'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Make sure detailed and edit view.
    for ($i = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {

      $num = $i + self::OPENSTACK_VOLUME_REPEAT_COUNT + 1;

      $this->drupalGet("/clouds/openstack/$cloud_context/volume/$num");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/volume/$num/edit");
      $this->assertSession()->linkExists($this->t('Detach'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/volume/$num/detach");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/volume/$num/delete");
    }

    // Update tags.
    for ($i = 0, $num = 1; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++, $num++) {

      // Update tags.
      $add[$i]['tags_name'] = $this->getRandomId();
      $this->updateTagsInMockData($i, 'Volumes', 'Name', $add[$i]['tags_name'], FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/volume");
    for ($i = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['tags_name']);
    }

    // Update tags for empty.
    for ($i = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'Volumes', 'Name', '', FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/volume");
    for ($i = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['tags_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Delete name tags.
    for ($i = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'Volumes', 'Name', '', TRUE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/volume");
    for ($i = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Delete Volume in mock data.
    for ($i = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {
      $this->deleteFirstVolumeMockData();
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/volume");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Volumes.'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Test the operation of creating snapshot.
   */
  public function testCreateSnapshotOperation(): void {
    $this->repeatTestCreateSnapshotOperation(
      self::OPENSTACK_VOLUME_REPEAT_COUNT
    );
  }

  /**
   * Repeat testing the operation of creating snapshot.
   *
   * @param int $max_count
   *   Max test repeating count.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Exception
   */
  private function repeatTestCreateSnapshotOperation($max_count): void {
    $cloud_context = $this->cloudContext;

    $add = $this->createVolumeTestFormData(self::OPENSTACK_VOLUME_REPEAT_COUNT, TRUE);
    for ($i = 0; $i < $max_count; $i++) {
      $this->reloadMockData();

      $snapshot_name = 'snapshot-name' . $this->random->name(10, TRUE);
      $this->updateDescribeSnapshotsMockData([['id' => $add[$i]['snapshot_id'], 'name' => $snapshot_name]]);
      $this->createSnapshotTestEntity(OpenStackSnapshot::class, $i, $add[$i]['snapshot_id'], $snapshot_name, $cloud_context);

      $this->drupalPostForm("/clouds/openstack/$cloud_context/volume/add",
                            $add[$i],
                            $this->t('Save'));
      $this->assertNoErrorMessage();

      // Make sure listing.
      $this->drupalGet("/clouds/openstack/$cloud_context/volume");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($this->t('Create Snapshot'));

      // Add a volume to DescribeVolumes.
      $volume_id = $this->latestTemplateVars['volume_id'];
      $add[$i]['name'] = $volume_id;
      $this->addVolumeMockData($add[$i], OpenStackVolume::TAG_CREATED_BY_UID);

      // Click "Create Snapshot" link.
      $this->clickLink($this->t('Create Snapshot'), $i);

      // Make sure creating page.
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($this->t('Add OpenStack Snapshot'));

      // Make sure the default value of field volume_id.
      $this->assertSession()->fieldValueEquals('volume_id', $volume_id);
    }
  }

  /**
   * Tests deleting volumes with bulk operation.
   *
   * @throws \Exception
   */
  public function testVolumeBulk(): void {
    $cloud_context = $this->cloudContext;

    for ($i = 0, $num = 0; $i < self::OPENSTACK_VOLUME_REPEAT_COUNT; $i++) {
      // Create volumes.
      $volumes = $this->createVolumesRandomTestFormData();
      $entities = [];
      foreach ($volumes ?: [] as $volume) {
        $entities[] = $this->createVolumeTestEntity(
          OpenStackVolume::class,
          $num++,
          $volume['VolumeId'],
          $volume['Name'],
          $cloud_context,
          Utils::getRandomUid()
        );
      }

      $this->runTestEntityBulk('volume', $entities);
    }
  }

}
