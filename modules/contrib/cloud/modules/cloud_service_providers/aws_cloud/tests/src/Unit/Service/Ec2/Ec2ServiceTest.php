<?php

namespace Drupal\Tests\aws_cloud\Unit\Service\Ec2;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Tests AWS Cloud Service.
 *
 * @group AWS Cloud
 */
class Ec2ServiceTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {

    // Create messenger, logger.factory and string_translation container.
    $container = new ContainerBuilder();

    // Messenger.
    $mock_messenger = $this->getMockBuilder(Messenger::class)
      ->disableOriginalConstructor()
      ->getMock();

    // Queue Factory.
    $mock_queue_factory = $this->getMockBuilder(QueueFactory::class)
      ->disableOriginalConstructor()
      ->getMock();

    // Logger.
    $mock_logger = $this->createMock(LoggerChannelInterface::class);
    $mock_logger_factory = $this->createMock(LoggerChannelFactoryInterface::class);
    $mock_logger_factory->expects($this->any())
      ->method('get')
      ->willReturn($mock_logger);

    // Set containers.
    $container->set('messenger', $mock_messenger);
    $container->set('logger.factory', $mock_logger_factory);
    $container->set('string_translation', $this->getStringTranslationStub());
    \Drupal::setContainer($container);

    $this->service = new Ec2ServiceMock(
      $this->createMock(EntityTypeManagerInterface::class),
      $this->getConfigFactoryStub([
        'aws_cloud.settings' => ['aws_cloud_test_mode' => TRUE],
      ]),
      $this->createMock(AccountInterface::class),
      $this->createMock(CloudConfigPluginManagerInterface::class),
      $this->createMock(FieldTypePluginManagerInterface::class),
      $this->createMock(EntityFieldManagerInterface::class),
      $this->createMock(LockBackendInterface::class),
      $mock_queue_factory
    );
  }

  /**
   * Testing get availability zones.
   */
  public function testGetAvailabilityZones(): void {
    $random = $this->getRandomGenerator();

    $zones = [];
    $zones[] = $random->name(8, TRUE);
    $zones[] = $random->name(8, TRUE);
    $zones[] = $random->name(8, TRUE);

    $responseZones = [];
    $responseZones['AvailabilityZones'] = array_map(static function ($zone) {
      return ['ZoneName' => $zone];
    }, $zones);
    $this->service->setAvailabilityZonesForTest($responseZones);

    $expectedResult = array_combine($zones, $zones);
    $actualResult = $this->service->getAvailabilityZones();
    $this->assertSame($expectedResult, $actualResult);
  }

}
