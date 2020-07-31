<?php

namespace Drupal\Tests\aws_cloud\Functional\Ec2;

use Drupal\aws_cloud\Entity\Ec2\KeyPair;
use Drupal\Tests\aws_cloud\Functional\AwsCloudTestBase;

/**
 * Tests AWS Cloud Key Pair.
 *
 * @group AWS Cloud
 */
class KeyPairTest extends AwsCloudTestBase {

  public const AWS_CLOUD_KEY_PAIR_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'list aws cloud key pair',
      'add aws cloud key pair',
      'view any aws cloud key pair',
      'edit any aws cloud key pair',
      'delete any aws cloud key pair',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getMockDataTemplateVars(): array {
    $key_fingerprint_parts = [];
    for ($i = 0; $i < 20; $i++) {
      $key_fingerprint_parts[] = sprintf('%02x', random_int(0, 255));
    }

    $key_material = '---- BEGIN RSA PRIVATE KEY ----'
      . $this->random->name(871, TRUE)
      . '-----END RSA PRIVATE KEY-----';
    return [
      'key_name' => $this->random->name(15, TRUE),
      'key_fingerprint' => implode(':', $key_fingerprint_parts),
      'key_material' => $key_material,
    ];
  }

  /**
   * Tests CRUD for Key Pair information.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testKeyPair(): void {
    $cloud_context = $this->cloudContext;

    // List Key Pair for Amazon EC2.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/key_pair");
    $this->assertNoErrorMessage();

    // Add a new Key Pair.
    $add = $this->createKeyPairTestFormData(self::AWS_CLOUD_KEY_PAIR_REPEAT_COUNT);
    for ($i = 0; $i < self::AWS_CLOUD_KEY_PAIR_REPEAT_COUNT; $i++) {
      $this->reloadMockData();

      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/key_pair/add",
                            $add[$i],
                            $this->t('Save'));

      // Make sure a warning message shows.
      $this->assertWarningMessage();
      $this->assertSession()->pageTextContains(strip_tags($this->t('Download private key. Once downloaded, the key will be deleted from the server.')));

      $t_args = ['@type' => 'Key Pair', '%label' => $add[$i]['key_pair_name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/key_pair");
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $i + 1; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['key_pair_name']);
      }
    }

    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_KEY_PAIR_REPEAT_COUNT; $i++, $num++) {
      // Make sure the all key_pair listing exists.
      $this->drupalGet('/clouds/aws_cloud/key_pair');
      $this->assertNoErrorMessage();

      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['key_pair_name']);
      }
    }

    // Delete Key Pair.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_KEY_PAIR_REPEAT_COUNT; $i++, $num++) {

      $this->drupalGet("/clouds/aws_cloud/$cloud_context/key_pair/$num/delete");
      $this->assertNoErrorMessage();
      $this->drupalPostForm("/clouds/aws_cloud/$cloud_context/key_pair/$num/delete",
                            [],
                            $this->t('Delete'));

      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'Key Pair', '@label' => $add[$i]['key_pair_name']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type @label has been deleted.', $t_args)));

      // Make sure listing.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/key_pair");
      $this->assertNoErrorMessage();
      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextNotContains($add[$i]['key_pair_name']);
      }
    }
  }

  /**
   * Tests deleting key pairs with bulk operation.
   *
   * @throws \Exception
   */
  public function testKeyPairBulk(): void {
    $cloud_context = $this->cloudContext;

    for ($i = 0; $i < self::AWS_CLOUD_KEY_PAIR_REPEAT_COUNT; $i++) {
      // Create key pairs.
      $key_pairs = $this->createKeyPairsRandomTestFormData();
      $index = 1;
      $entities = [];
      foreach ($key_pairs ?: [] as $key_pair) {
        $entities[] = $this->createKeyPairTestEntity(KeyPair::class, $index++, $key_pair['Name'], $key_pair['KeyFingerprint'], $cloud_context);
      }

      $this->runTestEntityBulk('key_pair', $entities);
    }
  }

  /**
   * Test updating key pair list.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testUpdateKeyPairList(): void {

    $cloud_context = $this->cloudContext;

    // Add a new Key Pair.
    $add = $this->createKeyPairTestFormData(self::AWS_CLOUD_KEY_PAIR_REPEAT_COUNT);
    for ($i = 0; $i < self::AWS_CLOUD_KEY_PAIR_REPEAT_COUNT; $i++) {
      $this->addKeyPairMockData($add[$i]['key_pair_name']);
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/key_pair");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::AWS_CLOUD_KEY_PAIR_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['key_pair_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Key Pairs.'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_KEY_PAIR_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['key_pair_name']);
    }

    // Make sure detailed and edit view.
    for ($i = 0, $num = 1; $i < self::AWS_CLOUD_KEY_PAIR_REPEAT_COUNT; $i++, $num++) {

      // Confirm the detailed view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/key_pair/$num");
      $this->assertSession()->linkExists($this->t('Edit'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/key_pair/$num/edit");
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/key_pair/$num/delete");
      $this->assertSession()->linkExists($this->t('List AWS Cloud Key Pairs'));
      // Click 'Refresh'.
      $this->clickLink($this->t('List AWS Cloud Key Pairs'));
      $this->assertNoErrorMessage();

      // Confirm the edit view.
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/key_pair/$num/edit");
      $this->assertSession()->linkNotExists($this->t('Edit'));
      $this->assertSession()->linkExists($this->t('Delete'));
      $this->assertSession()->linkByHrefExists("/clouds/aws_cloud/$cloud_context/key_pair/$num/delete");
    }

    // Edit Key Pair information.
    for ($i = 0; $i < self::AWS_CLOUD_KEY_PAIR_REPEAT_COUNT; $i++) {

      // Change Key Pair Name in mock data.
      $add[$i]['key_pair_name'] = $this->random->name(15, TRUE);
      $this->updateKeyPairMockData($i, $add[$i]['key_pair_name']);

    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/key_pair");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::AWS_CLOUD_KEY_PAIR_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['key_pair_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Key Pairs.'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_KEY_PAIR_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['key_pair_name']);
    }

    // Delete Key Pair in mock data.
    for ($i = 0; $i < self::AWS_CLOUD_KEY_PAIR_REPEAT_COUNT; $i++) {
      $this->deleteFirstKeyPairMockData();
    }

    // Make sure listing.
    $this->drupalGet("/clouds/aws_cloud/$cloud_context/key_pair");
    $this->assertNoErrorMessage();
    for ($i = 0; $i < self::AWS_CLOUD_KEY_PAIR_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextContains($add[$i]['key_pair_name']);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Key Pairs.'));
    // Make sure listing.
    for ($i = 0; $i < self::AWS_CLOUD_KEY_PAIR_REPEAT_COUNT; $i++) {
      $this->assertSession()->pageTextNotContains($add[$i]['key_pair_name']);
    }

  }

}
