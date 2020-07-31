<?php

namespace Drupal\Tests\aws_cloud\Functional\Ec2;

use Drupal\aws_cloud\Entity\Ec2\Volume;
use Drupal\aws_cloud\Entity\Ec2\Snapshot;
use Drupal\Tests\aws_cloud\Functional\AwsCloudTestBase;
use Drupal\Tests\cloud\Functional\Utils;

/**
 * Tests AWS Cloud Snapshot.
 *
 * @group AWS Cloud
 */
class SnapshotTest extends AwsCloudTestBase {

  public const AWS_CLOUD_SNAPSHOT_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list aws cloud snapshot',
      'add aws cloud snapshot',
      'view any aws cloud snapshot',
      'edit any aws cloud snapshot',
      'delete any aws cloud snapshot',

      'view any aws cloud volume',
      'add aws cloud volume',
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  protected function getMockDataTemplateVars(): array {
    return [
      'snapshot_id' => 'snap-' . $this->getRandomId(),
      'cidr_block' => Utils::getRandomCidr(),
      'group_id' => 'sg-' . $this->getRandomId(),
      'start_time' => date('c'),
    ];
  }

  /**
   * Tests CRUD for Snapshot information.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Exception
   */
  public function testSnapshot(): void {
    $cloud_context = $this->cloudContext;

    // List Snapshot for Amazon EC2.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/snapshot");
    $this->assertNoErrorMessage();

    // Create random volumes.
    $volumes = $this->createVolumesRandomTestFormData();
    $this->updateDescribeVolumesMockData($volumes);

    // Create the volume entities.
    $v = 1;
    foreach ($volumes ?: [] as $volume) {
      $this->createVolumeTestEntity(
        Volume::class,
        $v,
        $volume['VolumeId'],
        $volume['Name'],
        $cloud_context,
        Utils::getRandomUid()
      );
      $v++;
    }

    // Add a new Snapshot.
    $add = $this->createSnapshotTestFormData(self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT);
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      // Set volume ID.
      $add[$i]['volume_id'] = $volumes[array_rand($volumes)]['VolumeId'];

      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/snapshot/add",
                            $add[$i],
                            $this->t('Save'));

      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains(
        $this->t('Snapshot @name', ['@name' => $add[$i]['name']]));
      $this->assertSession()->pageTextContains($add[$i]['name']);

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/snapshot");
      $this->assertNoErrorMessage();
    }

    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all snapshot listing exists.
      $this->drupalGet('/clouds/aws_cloud/snapshot');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit an Snapshot information.
    $edit = $this->createSnapshotTestFormData(self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++, $num++) {

      unset(
        $edit[$i]['volume_id'],
        $edit[$i]['description']
      );

      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/snapshot/$num/edit",
                            $edit[$i],
                            $this->t('Save'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Snapshot', '%label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/snapshot");
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($edit[$i]['name']);
      }
    }

    // Delete Snapshot.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++, $num++) {

      $this->drupalGet("/clouds/aws_cloud/$cloud_context/snapshot/$num/delete");
      $this->assertNoErrorMessage();
      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/snapshot/$num/delete",
                            [],
                            $this->t('Delete'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Snapshot', '@label' => $edit[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/snapshot");
      $this->assertNoErrorMessage();
    }
  }

  /**
   * Test updating snapshots.
   */
  public function testUpdateSnapshot(): void {
    try {
      $this->repeatTestUpdateSnapshot(self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

  /**
   * Repeating test updating snapshot.
   *
   * @param int $max_count
   *   Max test repeating count.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Exception
   */
  private function repeatTestUpdateSnapshot($max_count): void {
    $cloud_context = $this->cloudContext;

    for ($i = 0; $i < $max_count; $i++) {
      $test_cases = $this->createUpdateSnapshotTestCases();
      $this->updateDescribeSnapshotsMockData($test_cases);
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/snapshot/update");
      $this->assertNoErrorMessage();

      foreach ($test_cases ?: [] as $test_case) {
        $this->assertSession()->linkExists(
          $test_case['name'] ?? $test_case['id']
        );
      }
    }
  }

  /**
   * Test updating snapshot list.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testUpdateSnapshotList() {

    $cloud_context = $this->cloudContext;

    // Add a new Snapshot.
    $add = $this->createSnapshotTestFormData(self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT);
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {
      $add[$i]['snapshot_id'] = $this->addSnapshotMockData($add[$i]['name'], $add[$i]['volume_id'], $add[$i]['description']);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/snapshot");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Snapshots.'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Make sure detailed and edit view.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++, $num++) {

      // Confirm the detailed view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/snapshot/$num");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/snapshot/$num/edit");
      $this->assertSession()->linkExists($this->t('Create Volume'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/volume/add?snapshot_id={$add[$i]['name']}");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/snapshot/$num/delete");
      $this->assertSession()->linkExists($this->t('List AWS Cloud Snapshots'));
      // Click 'Refresh'.
      $this->clickLink($this->t('List AWS Cloud Snapshots'));
      $this->assertNoErrorMessage();

      // Confirm the edit view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/snapshot/$num/edit");
      $this->assertSession()->linkNotExists($this->t('Edit'));
      $this->assertSession()->linkExists($this->t('Create Volume'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/volume/add?snapshot_id={$add[$i]['name']}");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/snapshot/$num/delete");
      $this->assertSession()->linkNotExists('Edit');

      // Click "Create Volume" link.
      $this->clickLink($this->t('Create Volume'));

      // Make sure creating page.
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($this->t('Add AWS Cloud Volume'));

      // Make sure the default value of field snapshot_id.
      $this->assertSession()->fieldValueEquals('snapshot_id', $add[$i]['name']);
    }

    // Edit Snapshot information.
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {

      // Change Snapshot Name in mock data.
      $add[$i]['name'] = 'snap-' . $this->getRandomId();
      $this->updateSnapshotMockData($i, $add[$i]['name']);

    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/snapshot");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Snapshots.'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Update tags for empty.
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'Snapshots', 'Name', '', FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/snapshot");
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['snapshot_id']);
    }

    // Delete name tags.
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'Snapshots', 'Name', '', TRUE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/snapshot");
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['snapshot_id']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['name']);
      $this->assertSession()->linkExists($add[$i]['snapshot_id']);
    }

    // Update tags.
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {

      $add[$i]['name'] = 'snap-' . $this->getRandomId();
      $this->updateTagsInMockData($i, 'Snapshots', 'Name', $add[$i]['name'], FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/snapshot");
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Delete Snapshot in mock data.
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {
      $this->deleteFirstSnapshotMockData();
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/snapshot");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Snapshots.'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Test the operation of creating volume.
   */
  public function testCreateVolumeOperation(): void {
    try {
      $this->repeatTestCreateVolumeOperation(
        self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT
      );
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

  /**
   * Repeat testing the operation of creating volume.
   *
   * @param int $max_count
   *   Max test repeating count.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Exception
   */
  private function repeatTestCreateVolumeOperation($max_count): void {
    $cloud_context = $this->cloudContext;

    // Create random volumes.
    $volumes = $this->createVolumesRandomTestFormData();
    $this->updateDescribeVolumesMockData($volumes);

    // Create the volume entities.
    $v = 1;
    foreach ($volumes ?: [] as $volume) {
      $this->createVolumeTestEntity(
        Volume::class,
        $v,
        $volume['VolumeId'],
        $volume['Name'],
        $cloud_context,
        Utils::getRandomUid()
      );
      $v++;
    }

    // Add a new Snapshot.
    $add = $this->createSnapshotTestFormData(self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT);
    for ($i = 0; $i < $max_count; $i++) {
      $this->reloadMockData();

      // Set volume ID.
      $add[$i]['volume_id'] = $volumes[array_rand($volumes)]['VolumeId'];
      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/snapshot/add",
                            $add[$i],
                            $this->t('Save'));
      $this->assertNoErrorMessage();

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/snapshot");
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($this->t('Create Volume'));

      // Add snapshot to DescribeSnapshots of Mock data.
      $snapshot_id = $this->latestTemplateVars['snapshot_id'];
      $this->addDescribeSnapshotsMockData($snapshot_id);

      // Click "Create Volume" link.
      $this->clickLink($this->t('Create Volume'), $i);

      // Make sure creating page.
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($this->t('Add AWS Cloud Volume'));

      // Make sure the default value of field snapshot_id.
      $this->assertSession()->fieldValueEquals('snapshot_id', $snapshot_id);
    }
  }

  /**
   * Tests deleting snapshots with bulk operation.
   *
   * @throws \Exception
   */
  public function testSnapshotBulk(): void {
    $cloud_context = $this->cloudContext;

    for ($i = 0; $i < self::AWS_CLOUD_SNAPSHOT_REPEAT_COUNT; $i++) {
      // Create snapshots.
      $snapshots = $this->createSnapshotsRandomTestFormData();
      $index = 0;
      $entities = [];
      foreach ($snapshots ?: [] as $snapshot) {
        $entities[] = $this->createSnapshotTestEntity(Snapshot::class, $index++, $snapshot['SnapshotId'], $snapshot['Name'], $cloud_context);
      }

      $this->runTestEntityBulk('snapshot', $entities);
    }
  }

}
