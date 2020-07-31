<?php

namespace Drupal\Tests\cloud\Functional;

use Drupal\cloud\Entity\CloudConfig;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Component\Utility\Random;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Tests\system\Functional\Module\ModuleTestBase;
use Psr\Log\LoggerInterface;

/**
 * Tests cloud credit.
 *
 * @group Cloud
 */
class CloudContentEntityTraitTest extends ModuleTestBase {

  use CloudContentEntityTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'claro';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'cloud',
    'aws_cloud',
    'dblog',
  ];

  /**
   * The random object.
   *
   * @var \Drupal\Component\Utility\Random
   */
  protected $random;

  /**
   * The trait object to be tested.
   *
   * @var \Drupal\cloud\Traits\CloudContentEntityTrait
   */
  protected $traitObject;

  /**
   * The dummy entity to be used in this test.
   *
   * @var \Drupal\cloud\Entity\CloudConfig
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'access content',
    ];
  }

  /**
   * Set up test.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUp() {

    parent::setUp();

    if (!$this->random) {
      $this->random = new Random();
    }

    $this->entity = CloudConfig::create([
      'type' => $this->random->name(),
      'name' => $this->random->name(),
    ]);
    $this->entity->save();

    $this->traitObject = new TestCloudContentEntityTrait();
  }

  /**
   * Test the entity type name.
   *
   * - Test the entity type name with the format underscore.
   * - Test the entity type name with the format whitespace.
   * - Test the entity type name with the format camel.
   * - Test the entity type name plural with the format camel.
   * - Test an entity_type and get the plural form.
   */
  public function testGetEntityTypeName(): void {

    $entity = $this->entity;

    // Given entity type name: 'cloud_config', expected output: 'config'.
    $expected = 'config';
    $this->assertEquals($this->traitObject->getShortEntityTypeNameUnderscore($entity), $expected);

    // Given entity type name: 'cloud_config', expected output: 'Config'.
    $expected = 'Config';
    $this->assertEquals($this->traitObject->getShortEntityTypeNameWhitespace($entity), $expected);

    // Given entity type name: 'cloud_config', expected output: 'Config'.
    $expected = 'Config';
    $this->assertEquals($this->traitObject->getShortEntityTypeNameCamel($entity), $expected);

    // Given entity type name: 'cloud_config', expected output: 'Configs'.
    $expected = 'Configs';
    $this->assertEquals($this->traitObject->getShortEntityTypeNamePluralCamel($entity), $expected);

    // Given entity type name: 'cloud_config',
    // expected output: 'Cloud Service Provider', 'Cloud Service Providers'.
    $expected = [
      'singular' => 'cloud service provider',
      'plural' => 'cloud service providers',
    ];
    $labels = $this->traitObject->getDisplayLabels($entity->getEntityTypeId());
    $this->assertEquals($labels, $expected);

    // Given entity provider name: 'cloud', expected output: 'cloud'.
    $expected = 'cloud';
    $this->assertEquals($this->traitObject->getModuleName($entity), $expected);

    // Given entity provider name: 'cloud', expected output: 'cloud'.
    $expected = 'cloud';
    $this->assertEquals($this->traitObject->getModuleNameWhitespace($entity), $expected);
  }

  /**
   * Test a status message and log its notice.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testProcessOperationStatus(): void {

    $entity = $this->entity;
    $channel = $entity->getEntityType()->getProvider();

    // Create test case.
    $passive_operation = 'created';

    // Assert the status message.
    $t_args = [
      '@type' => $entity->getEntityType()->getSingularLabel(),
      '%label' => $entity->toLink($entity->label())->toString(),
      '@passive_operation' => $passive_operation,
    ];
    $expected = $this->t('The @type %label has been @passive_operation.', $t_args);
    $this->assertEqual($this->traitObject->getOperationMessage($entity, $passive_operation), $expected);

    // Assert the log message.
    $this->traitObject->logOperationMessage($entity, $passive_operation);
    $variables = [
      '@type' => $entity->getEntityType()->getLabel(),
      '@passive_operation' => $passive_operation,
      '%label' => $entity->label(),
    ];
    $link = $entity->toLink($this->t('View'))->toString();
    $this->assertLogMessage($channel, '@type: @passive_operation %label.', $variables, RfcLogLevel::NOTICE, $link);

    // Delete test case.
    $passive_operation = 'deleted';

    // Assert the status message.
    $t_args = [
      '@type' => $entity->getEntityType()->getSingularLabel(),
      '%label' => $entity->label(),
      '@passive_operation' => $passive_operation,
    ];
    $expected = $this->t('The @type %label has been @passive_operation.', $t_args);
    $this->assertEqual($this->traitObject->getOperationMessage($entity, $passive_operation), $expected);

    // Assert the log message.
    $this->traitObject->logOperationMessage($entity, $passive_operation);
    $variables = [
      '@type' => $entity->getEntityType()->getLabel(),
      '@passive_operation' => $passive_operation,
      '@label' => $entity->label(),
    ];
    $this->assertLogMessage($channel, '@type: @passive_operation @label.', $variables, RfcLogLevel::NOTICE);
  }

  /**
   * Test a status error message and log its error.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testProcessOperationErrorStatus(): void {

    $entity = $this->entity;
    $channel = $entity->getEntityType()->getProvider();

    // Create test case.
    $passive_operation = 'created';

    // Assert the status message.
    $t_args = [
      '@type' => $entity->getEntityType()->getSingularLabel(),
      '@label' => $entity->label(),
      '@passive_operation' => $passive_operation,
    ];

    $expected = $this->t('The @type @label could not be @passive_operation.', $t_args);
    $this->assertEqual($this->traitObject->getOperationErrorMessage($entity, $passive_operation), $expected);

    // Assert the log message.
    $this->traitObject->logOperationErrorMessage($entity, $passive_operation);
    $variables = [
      '@type' => $entity->getEntityType()->getLabel(),
      '@label' => $entity->label(),
      '@passive_operation' => $passive_operation,
    ];
    $this->assertLogMessage($channel, '@type: @label could not be @passive_operation.', $variables, RfcLogLevel::ERROR);

    // Delete test case.
    $passive_operation = 'deleted';

    // Assert the status message.
    $t_args = [
      '@type' => $entity->getEntityType()->getSingularLabel(),
      '@passive_operation' => $passive_operation,
      '%label' => $entity->toLink($entity->label())->toString(),
    ];

    $expected = $this->t('The @type %label could not be @passive_operation.', $t_args);
    $this->assertEqual($this->traitObject->getOperationErrorMessage($entity, $passive_operation), $expected);

    // Assert the log message.
    $this->traitObject->logOperationErrorMessage($entity, $passive_operation);
    $variables = [
      '@type' => $entity->getEntityType()->getLabel(),
      '%label' => $entity->label(),
      '@passive_operation' => $passive_operation,
    ];
    $link = $entity->toLink($this->t('View'))->toString();
    $this->assertLogMessage($channel, '@type: %label could not be @passive_operation.', $variables, RfcLogLevel::ERROR, $link);
  }

}

/**
 * A stub class for CloudContentEntityTrait.
 */
class TestCloudContentEntityTrait {

  use CloudContentEntityTrait {
    getShortEntityTypeNameUnderscore as public;
    getShortEntityTypeNameWhitespace as public;
    getShortEntityTypeNameCamel as public;
    getShortEntityTypeNamePluralCamel as public;
    getDisplayLabels as public;
    getModuleNameWhitespace as public;
    getModuleName as public;
    getOperationMessage as public;
    logOperationMessage as public;
    getOperationErrorMessage as public;
    logOperationErrorMessage as public;
  }

  /**
   * Gets the logger for a specific channel.
   *
   * This method exists for backward-compatibility between FormBase and
   * LoggerChannelTrait. Use LoggerChannelTrait::getLogger() instead.
   *
   * @param string $channel
   *   The name of the channel. Can be any string, but the general practice is
   *   to use the name of the subsystem calling this.
   *
   * @return \Psr\Log\LoggerInterface
   *   The logger for the given channel.
   */
  public function logger($channel): LoggerInterface {
    return $this->getLogger($channel);
  }

}
