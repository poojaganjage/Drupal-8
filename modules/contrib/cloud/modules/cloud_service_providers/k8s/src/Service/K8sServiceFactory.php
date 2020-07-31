<?php

namespace Drupal\k8s\Service;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Session\AccountInterface;

/**
 * K8sService service interacts with the K8s API.
 */
class K8sServiceFactory {

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Cloud context string.
   *
   * @var string
   */
  protected $cloudContext;

  /**
   * The config factory.
   *
   * Subclasses should use the self::config() method (or use
   * ConfigFormBaseTrait), which may be overridden to address specific needs
   * when loading config, rather than this property directly.
   * See \Drupal\Core\Form\ConfigFormBase::config() for an example of this.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Field type plugin manager.
   *
   * @var \Drupal\core\Field\FieldTypePluginManagerInterface
   */
  protected $fieldTypePluginManager;

  /**
   * Entity field manager interface.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * TRUE to run the operation.  FALSE to run the operation in validation mode.
   *
   * @var bool
   */
  private $dryRun;

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
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The K8s service.
   *
   * @var \Drupal\k8s\Service\K8sServiceInterface
   */
  protected static $k8sService;

  /**
   * Constructs a new K8sService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   An entity type manager instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   * @param \Drupal\core\Field\FieldTypePluginManagerInterface $field_type_plugin_manager
   *   The field type plugin manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock interface.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
    AccountInterface $current_user,
    CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
    FieldTypePluginManagerInterface $field_type_plugin_manager,
    EntityFieldManagerInterface $entity_field_manager,
    LockBackendInterface $lock,
    QueueFactory $queue_factory,
    ModuleHandlerInterface $module_handler
  ) {
    // Setup the entity type manager for querying entities.
    $this->entityTypeManager = $entity_type_manager;

    // Setup the configuration factory.
    $this->configFactory = $config_factory;

    $this->currentUser = $current_user;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
    $this->fieldTypePluginManager = $field_type_plugin_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->lock = $lock;
    $this->queueFactory = $queue_factory;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Create service.
   *
   * @return \Drupal\k8s\Service\K8sServiceInterface
   *   K8s service.
   */
  public function create() {
    $test_mode = (bool) $this->configFactory->get('k8s.settings')->get('k8s_test_mode');
    $class_name = K8sService::class;
    if ($test_mode) {
      $class_name = K8sServiceMock::class;
    }

    return new $class_name(
      $this->entityTypeManager,
      $this->configFactory,
      $this->currentUser,
      $this->cloudConfigPluginManager,
      $this->fieldTypePluginManager,
      $this->entityFieldManager,
      $this->lock,
      $this->queueFactory,
      $this->moduleHandler
    );
  }

}
