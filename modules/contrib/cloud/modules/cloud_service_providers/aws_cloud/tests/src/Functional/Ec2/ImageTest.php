<?php

namespace Drupal\Tests\aws_cloud\Functional\Ec2;

use Drupal\aws_cloud\Entity\Ec2\Image;
use Drupal\Tests\aws_cloud\Functional\AwsCloudTestBase;

/**
 * Tests AWS Cloud Image.
 *
 * @group AWS Cloud
 */
class ImageTest extends AwsCloudTestBase {

  public const AWS_CLOUD_IMAGE_REPEAT_COUNT = 2;

  public const AWS_UPDATE_IMAGE_LIST_REFRESH_TIME_ADJUSTMENT = 10 * 60;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'add aws cloud image',
      'list aws cloud image',
      'view any aws cloud image',
      'edit any aws cloud image',
      'delete any aws cloud image',
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
      'name' => "ImageTest::getMockDataTemplateVars - {$this->random->name(8, TRUE)}",
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

    // List Image for Amazon EC2.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Images.'));
    $this->assertNoErrorMessage();

    // Register a new Image.
    $add = $this->createImageTestFormData(self::AWS_CLOUD_IMAGE_REPEAT_COUNT);

    // 3 times.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++, $num++) {

      // Need to process addImageMockeData before saving the form data since
      // ImageCreateForm::save uses createImage method.
      $images[] = $this->addImageMockData($add[$i]['name'], $add[$i]['description'], $cloud_context);
      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/image/add",
                            $add[$i],
                            $this->t('Save'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Image', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure View.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/image/$num");
      $this->assertNoErrorMessage();

      // Make sure listing.
      // Click 'Refresh'.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");
      $this->clickLink($this->t('Refresh'));
      $this->assertSession()->pageTextContains($this->t('Updated Images.'));
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all image listing exists.
      $this->drupalGet('/clouds/aws_cloud/image');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name']);
      }
    }

    // Edit an Image information.
    $edit = $this->createImageTestFormData(self::AWS_CLOUD_IMAGE_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++, $num++) {

      unset($edit[$i]['instance_id']);

      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/image/$num/edit",
                            $edit[$i],
                            $this->t('Save'));

      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($add[$i]['name']);

      // Make sure the description.
      $this->assertSession()->fieldValueEquals('description', $edit[$i]['description']);
      $t_args = ['@type' => 'Image', '%label' => $add[$i]['name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");
      $this->assertNoErrorMessage();
      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$i]['name']);
      }

    }

    // Delete Image.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++, $num++) {

      $state = $images[$i]['State'];
      switch ($state) {

        case 'pending':
          // Make sure of the message.
          $this->drupalGet("/clouds/aws_cloud/$cloud_context/image/$num/delete");
          $this->assertNoErrorMessage();
          $this->assertSession()->pageTextContains($this->t('Cannot delete an image in @state state', [
            '@state' => $state,
          ]));
          break;

        // An image can delete in 'available' or 'failed' state.
        case 'available':
        case 'failed':
          // Delete image.
          $this->deleteImage($cloud_context, $num, $edit[$i]['name']);

          // Make sure listing.
          $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");
          $this->assertNoErrorMessage();
          for ($j = 0; $j < $num; $j++) {
            $this->assertSession()->pageTextNotContains($edit[$i]['name']);
          }
          break;
      }

    }
  }

  /**
   * Test Import image.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Exception
   */
  public function testImportImage(): void {
    $cloud_context = $this->cloudContext;
    $images = $this->createImagesRandomTestFormData();

    // Pick up the first element of the image data.  Handle only one image to
    // import from here.
    $image_id = $images[0]['ImageId'];
    $name = $images[0]['Name'];
    $product_code1 = $images[0]['ProductCodes'][0]['ProductCode'];
    $product_code2 = $images[0]['ProductCodes'][1]['ProductCode'];
    $this->updateImagesMockData([$images[0]]);

    // Import image.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/images/import");
    $this->assertNoErrorMessage();

    $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/images/import",
                          ['image_ids' => $image_id],
                          $this->t('Import'));
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains('Imported 1 images');
    $this->assertSession()->pageTextContains($image_id);

    $num = 1;

    // View image.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/image/$num");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($image_id);
    $this->assertSession()->pageTextContains("$product_code1,$product_code2");

    switch ($images[0]['State']) {

      case 'pending':
        // Make sure of the message.
        $this->drupalGet("/clouds/aws_cloud/$cloud_context/image/$num/delete");
        $this->assertNoErrorMessage();
        $this->assertSession()->pageTextContains($this->t('Cannot delete an image in @state state', [
          '@state' => $images[0]['State'],
        ]));
        break;

      case 'available':
      case 'failed':
        // Delete image.
        $this->deleteImage($cloud_context, $num, $name);

        // Make sure listing.
        $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");
        $this->assertNoErrorMessage();
        $this->assertSession()->pageTextNotContains($name);
        break;
    }
  }

  /**
   * Tests deleting images with bulk operation.
   */
  public function testImageBulk(): void {
    $cloud_context = $this->cloudContext;

    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++) {
      // Create images.
      $images = $this->createImagesRandomTestFormData();
      $index = 0;
      $entities = [];
      foreach ($images ?: [] as $image) {
        $entities[] = $this->createImageTestEntity(Image::class, $index++, $image['ImageId'], $cloud_context);
      }

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
    $add = $this->createImageTestFormData(self::AWS_CLOUD_IMAGE_REPEAT_COUNT);
    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++) {
      $this->addImageMockData($add[$i]['name'], $add[$i]['description'], $cloud_context);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Images.'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Make sure detailed and edit view.
    $num = 1;
    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++, $num++) {

      // Confirm the detailed view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/image/$num");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/image/$num/edit");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/image/$num/delete");
      $this->assertSession()->linkExists($this->t('List AWS Cloud Images'));
      // Click 'Refresh'.
      $this->clickLink($this->t('List AWS Cloud Images'));
      $this->assertNoErrorMessage();

      // Confirm the edit view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/image/$num/edit");
      $this->assertSession()->linkNotExists($this->t('Edit'));
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/image/$num/delete");
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
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextNotContains($data['name']);

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Images.'));
    $add = array_merge($add, [$data]);
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT + 1; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Update tags.
    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++) {

      $add[$i]['tags_name'] = $this->getRandomId();
      $this->updateTagsInMockData($i, 'Images', 'Name', $add[$i]['tags_name'], FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");
    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['tags_name']);
    }

    // Update tags for empty.
    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'Images', 'Name', '', FALSE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");
    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkExists($add[$i]['tags_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Delete name tags.
    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++) {

      // Update tags.
      $this->updateTagsInMockData($i, 'Images', 'Name', '', TRUE);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");
    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT; $i++) {
      $this->assertSession()->linkNotExists($add[$i]['tags_name']);
      $this->assertSession()->linkExists($add[$i]['name']);
    }

    // Change refreshed time of entities.
    $entity_type_manager = \Drupal::entityTypeManager();
    $entities = $entity_type_manager
      ->getStorage('aws_cloud_image')->loadByProperties([
        'cloud_context' => $cloud_context,
      ]);

    foreach ($entities ?: [] as $entity) {
      $timestamp = time();
      $timestamp -= self::AWS_UPDATE_IMAGE_LIST_REFRESH_TIME_ADJUSTMENT;
      $entity->setRefreshed($timestamp);
      $entity->save();
    }

    // Delete Image in mock data.
    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT + 1; $i++) {
      $this->deleteFirstImageMockData();
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/image");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT + 1; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Images.'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_IMAGE_REPEAT_COUNT + 1; $i++) {
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
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/image/$num/delete");
    $this->assertNoErrorMessage();
    $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/image/$num/delete",
                          [],
                          $this->t('Delete'));

    $this->assertNoErrorMessage();
    $t_args = ['@type' => 'Image', '@label' => $name];
    $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));
  }

}
