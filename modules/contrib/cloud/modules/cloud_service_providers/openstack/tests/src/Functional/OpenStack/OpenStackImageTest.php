<?php

namespace Drupal\Tests\openstack\Functional\OpenStack;

use Drupal\openstack\Entity\OpenStackImage;
use Drupal\Tests\openstack\Functional\OpenStackTestBase;

/**
 * Tests OpenStack Image.
 *
 * @group OpenStack
 */
class OpenStackImageTest extends OpenStackTestBase {

  public const OPENSTACK_IMAGE_REPEAT_COUNT = 2;

  public const OPENSTACK_UPDATE_IMAGE_LIST_REFRESH_TIME_ADJUSTMENT = 10 * 60;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'add openstack image',
      'list openstack images',
      'view any openstack image',
      'edit any openstack image',
      'delete any openstack image',
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  protected function getMockDataTemplateVars(): array {
    $architecture = ['x86_64', 'arm64'];
    $image_type = ['machine', 'kernel', 'ramdisk'];
    $state = ['available', 'pending', 'failed'];
    $hypervisor = ['ovm', 'xen'];
    $public = [0, 1];
    return [
      'image_id' => 'ami-' . $this->getRandomId(),
      'account_id' => random_int(100000000000, 999999999999),
      'name' => "OpenStackImageTest::getMockDataTemplateVars - {$this->random->name(8, TRUE)}",
      'kernel_id' => 'aki-' . $this->getRandomId(),
      'ramdisk_id' => 'ari-' . $this->getRandomId(),
      'product_code1' => $this->random->name(8, TRUE),
      'product_code2' => $this->random->name(8, TRUE),
      'image_location' => $this->random->name(16, TRUE),
      'state_reason_message' => $this->random->name(8, TRUE),
      'platform' => $this->random->name(8, TRUE),
      'description' => $this->random->string(8, TRUE),
      'creation_date' => date('c'),
      'architecture' => $architecture[array_rand($architecture)],
      'image_type' => $image_type[array_rand($image_type)],
      'state' => $state[array_rand($state)],
      'hypervisor' => $hypervisor[array_rand($hypervisor)],
      'public' => $public[array_rand($public)],
    ];
  }

  /**
   * Tests CRUD for image information.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testImage(): void {
    $cloud_context = $this->cloudContext;
    $images = [];

    // Initialize all mock data in DescribeImages.
    $this->updateMockDataToConfig([
      'DescribeImages' => [
        'Images' => [],
      ],
    ]);

    // List Image for OpenStack.
    $this->drupalGet("/clouds/openstack/$cloud_context/image");
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Images.'));
    $this->assertNoErrorMessage();

    // Register a new Image.
    $add = $this->createImageTestFormData(self::OPENSTACK_IMAGE_REPEAT_COUNT);

    // 3 times.
    for ($i = 0, $num = 1; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++, $num++) {

      // Need to process addImageMockeData before saving the form data since
      // ImageCreateForm::save uses createImage method.
      $images[] = $this->addImageMockData($add[$i]['name'], $add[$i]['description'], $cloud_context);
      $this->drupalPostForm("/clouds/openstack/$cloud_context/image/add",
                            $add[$i],
                            $this->t('Save'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Image', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure View.
      $this->drupalGet("/clouds/openstack/$cloud_context/image/$num");
      $this->assertNoErrorMessage();

      // Make sure listing.
      // Click 'Refresh'.
      $this->drupalGet("/clouds/openstack/$cloud_context/image");
      $this->clickLink($this->t('Refresh'));
      $this->assertSession()->pageTextContains($this->t('Updated Images.'));
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    for ($i = 0, $num = 1; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all image listing exists.
      $this->drupalGet('/clouds/openstack/image');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit an Image information.
    $edit = $this->createImageTestFormData(self::OPENSTACK_IMAGE_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++, $num++) {

      unset($edit[$i]['instance_id']);

      $this->drupalPostForm("/clouds/openstack/$cloud_context/image/$num/edit",
                            $edit[$i],
                            $this->t('Save'));
      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);

      // Make sure the description.
      $this->assertSession()->fieldValueEquals('description', $edit[$i]['description']);
      $t_args = ['@type' => 'Image', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/openstack/$cloud_context/image");
      $this->assertNoErrorMessage();
      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$i]['name']);
      }

    }

    // Delete Image.
    for ($i = 0, $num = 1; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++, $num++) {

      $state = $images[$i]['State'];
      switch ($state) {

        case 'pending':
          // Make sure of the message.
          $this->drupalGet("/clouds/openstack/$cloud_context/image/$num/delete");
          $this->assertNoErrorMessage();
          $this->assertSession()->pageTextContains($this->t('Cannot delete an image in @state state', [
            '@state' => $state,
          ]));
          break;

        // Only an image can delete in 'available' state.
        case 'available':
        case 'failed':
          // Delete image.
          $this->deleteImage($cloud_context, $num, $edit[$i]['name']);

          // Make sure listing.
          $this->drupalGet("/clouds/openstack/$cloud_context/image");
          $this->assertNoErrorMessage();
          for ($j = 0; $j < $num; $j++) {
            $this->assertSession()->pageTextNotContains($edit[$i]['name']);
          }
          break;
      }
    }
  }

  /**
   * Tests deleting images with bulk operation.
   *
   * @throws \Exception
   */
  public function testImageBulk(): void {
    $cloud_context = $this->cloudContext;

    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++) {
      // Create images.
      $images = $this->createImagesRandomTestFormData();
      $index = 0;
      $entities = [];
      foreach ($images ?: [] as $image) {
        $entities[] = $this->createImageTestEntity(OpenStackImage::class, $index++, $image['ImageId'], $cloud_context);
      }

      // The first parameter type should be 'image' in OpenStack.
      $this->runTestEntityBulk('image', $entities);
    }
  }

  /**
   * Test updating image list.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testUpdateImageList(): void {
    $cloud_context = $this->cloudContext;

    // Delete init mock data.
    $this->deleteFirstImageMockData();

    // Add a new Image.
    $add = $this->createImageTestFormData(self::OPENSTACK_IMAGE_REPEAT_COUNT);
    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++) {
      $this->addImageMockData($add[$i]['name'], $add[$i]['description'], $cloud_context);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/image");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Images.'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Make sure detailed and edit view.
    $num = 1;
    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++, $num++) {

      // Confirm the detailed view.
      $this->drupalGet("/clouds/openstack/$cloud_context/image/$num");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/image/$num/edit");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/image/$num/delete");
      $this->assertSession()->linkExists($this->t('List OpenStack Images'));
      // Click 'Refresh'.
      $this->clickLink($this->t('List OpenStack Images'));
      $this->assertNoErrorMessage();

      // Confirm the edit view.
      $this->drupalGet("/clouds/openstack/$cloud_context/image/$num/edit");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/image/$num/edit");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/openstack/$cloud_context/image/$num/delete");
    }

    // Add a new Image.
    $num++;
    $data = [
      'name'        => "Image #$num - " . date('Y/m/d - ') . $this->random->name(8, TRUE),
      'instance_id' => 'i-' . $this->getRandomId(),
      'description' => 'description-' . $this->random->name(64),
    ];
    $this->addImageMockData($data['name'], $data['description'], $cloud_context);

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/image");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextNotContains($data['name']);

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Images.'));
    $add = array_merge($add, [$data]);
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT + 1; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Update tags.
    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++) {

      $add[$i]['tags_name'] = $this->getRandomId();
      $this->updateTagsInMockData($i, 'Images', 'Name', $add[$i]['tags_name'], FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/image");
    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['tags_name']);
    }

    // Update tags for empty.
    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'Images', 'Name', '', FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/image");
    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['tags_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Delete name tags.
    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'Images', 'Name', '', TRUE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/image");
    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Change refreshed time of entities.
    $entity_type_manager = \Drupal::entityTypeManager();
    $entities = $entity_type_manager->getStorage('openstack_image')->loadByProperties(
      ['cloud_context' => $cloud_context]
    );

    foreach ($entities ?: [] as $entity) {
      $timestamp = time();
      $timestamp -= self::OPENSTACK_UPDATE_IMAGE_LIST_REFRESH_TIME_ADJUSTMENT;
      $entity->setRefreshed($timestamp);
      $entity->save();
    }

    // Delete Image in mock data.
    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT + 1; $i++) {
      $this->deleteFirstImageMockData();
    }

    // Make sure listing.
    $this->drupalGet("/clouds/openstack/$cloud_context/image");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT + 1; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Images.'));
    // Make sure listing.
    for ($i = 0; $i < self::OPENSTACK_IMAGE_REPEAT_COUNT + 1; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }
  }

  /**
   * Delete Image.
   *
   * @param string $cloud_context
   *   Cloud context.
   * @param int $num
   *   Delete image number.
   * @param string $name
   *   Delete image name.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  private function deleteImage($cloud_context, $num, $name): void {
    $this->drupalGet("/clouds/openstack/$cloud_context/image/$num/delete");
    $this->assertNoErrorMessage();
    $this->drupalPostForm("/clouds/openstack/$cloud_context/image/$num/delete",
                          [],
                          $this->t('Delete'));

    $this->assertNoErrorMessage();
    $t_args = ['@type' => 'Image', '@label' => $name];
    $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));
  }

}
