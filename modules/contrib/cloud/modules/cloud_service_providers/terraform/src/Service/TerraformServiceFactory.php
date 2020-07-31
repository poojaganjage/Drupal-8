<?php

namespace Drupal\terraform\Service;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Service\CloudServiceBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Queue\QueueFactory;

/**
 * TerraformService service interacts with the Terraform API.
 */
class TerraformServiceFactory extends CloudServiceBase {

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $stringTranslation;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory.
   *
   * Subclasses should use the self::config() method, which may be overridden to
   * address specific needs when loading config, rather than this property
   * directly. See \Drupal\Core\Form\ConfigFormBase::config() for an example of
   * this.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Guzzle client factory.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  protected $clientFactory;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * The lock interface.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * The queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * Constructs a new TerraformService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   An entity type manager instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory.
   * @param \Drupal\Core\Http\ClientFactory $client_factory
   *   The http client.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock interface.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory,
                              ClientFactory $client_factory,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
                              LockBackendInterface $lock,
                              QueueFactory $queue_factory) {

    // The parent constructor takes care of $this->messenger object.
    parent::__construct();

    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->clientFactory = $client_factory;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
    $this->lock = $lock;
    $this->queueFactory = $queue_factory;
  }

  /**
   * Create service.
   *
   * @return \Drupal\terraform\Service\TerraformServiceInterface
   *   Terraform service.
   */
  public function create() {
    $test_mode = (bool) $this->configFactory->get('terraform.settings')->get('terraform_test_mode');
    $class_name = TerraformService::class;
    if ($test_mode) {
      $class_name = TerraformServiceMock::class;
    }

    return new $class_name(
      $this->entityTypeManager,
      $this->configFactory,
      $this->clientFactory,
      $this->cloudConfigPluginManager,
      $this->lock,
      $this->queueFactory
    );
  }

}
