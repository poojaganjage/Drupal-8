<?php

namespace Drupal\Tests\aws_cloud\Functional\Ec2;

use Drupal\Tests\aws_cloud\Functional\AwsCloudTestBase;

/**
 * Tests AWS Cloud Instance Type Price.
 *
 * @group AWS Cloud
 */
class InstanceTypePriceControllerTest extends AwsCloudTestBase {

  public const AWS_CLOUD_PRICE_REPEAT_COUNT = 2;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'view aws cloud instance type prices',
    ];
  }

  /**
   * Tests displaying prices.
   */
  public function testShowPrice(): void {
    try {
      $this->repeatTestShowPrice(self::AWS_CLOUD_PRICE_REPEAT_COUNT);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
  }

  /**
   * Repeats testing displaying prices.
   *
   * @param int $max_test_repeat_count
   *   Max test repeating count.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  private function repeatTestShowPrice($max_test_repeat_count = 1): void {
    $cloud_context = $this->cloudContext;

    $instance_type_data = aws_cloud_get_instance_types($cloud_context);

    for ($i = 0; $i < $max_test_repeat_count; $i++) {
      $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance_type_price");

      $this->assertNoErrorMessage();
      $this->assertSession()->pageTextContains($this->t('AWS Cloud Instance Type Prices'));
      foreach ($instance_type_data ?: [] as $data) {
        $parts = explode(':', $data);
        $this->assertSession()->pageTextContains($parts[0]);
      }
    }
  }

  /**
   * Tests displaying prices.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testUpdatePrice(): void {
    $cloud_context = $this->cloudContext;
    $instance_types = aws_cloud_get_instance_types($cloud_context);

    $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance_type_price");

    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($this->t('AWS Cloud Instance Type Prices'));
    foreach ($instance_types ?: [] as $instance_type_data) {
      $parts = explode(':', $instance_type_data);
      $this->assertSession()->pageTextContains($parts[0]);
    }

    // Reset the cache.
    $cloud_config_plugin = \Drupal::service('plugin.manager.cloud_config_plugin');
    $cloud_config_plugin->setCloudContext($cloud_context);
    $cloud_config = $cloud_config_plugin->loadConfigEntity();
    $cache_key = _aws_cloud_get_instance_type_cache_key_by_region($cloud_config->get('field_region')->value);
    \Drupal::cache()->set($cache_key, []);

    \Drupal::service('router.builder')->rebuild();

    $this->drupalGet("/clouds/aws_cloud/$cloud_context/instance_type_price");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($this->t('AWS Cloud Instance Type Prices'));
    foreach ($instance_types ?: [] as $instance_type_data) {
      $parts = explode(':', $instance_type_data);
      $this->assertSession()->pageTextNotContains($parts[0]);
    }

    // Click 'Refresh'.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated Instance Type Prices.'));
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($this->t('AWS Cloud Instance Type Prices'));
    $new_instance_types = aws_cloud_get_instance_types($cloud_context);
    foreach ($new_instance_types ?: [] as $instance_type_data) {
      $parts = explode(':', $instance_type_data);
      $this->assertSession()->pageTextContains($parts[0]);
    }
  }

}
