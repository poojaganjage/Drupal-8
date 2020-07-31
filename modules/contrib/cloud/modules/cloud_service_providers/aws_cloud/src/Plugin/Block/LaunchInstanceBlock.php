<?php

namespace Drupal\aws_cloud\Plugin\Block;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block with links to launch an instance.
 *
 * @Block(
 *   id = "aws_cloud_launch_instance_block",
 *   admin_label = @Translation("Launch Instances"),
 *   category = @Translation("AWS Cloud")
 * )
 */
class LaunchInstanceBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * Creates a ResourcesBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   An entity type manager instance.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    AccountInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager,
    CloudConfigPluginManagerInterface $cloud_config_plugin_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.cloud_config_plugin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $entities = $this->cloudConfigPluginManager->loadConfigEntities('aws_cloud');
    $build = [];
    $build['instances'] = [
      '#type' => 'details',
      '#title' => $this->t('Launch Instances'),
      '#open' => TRUE,
    ];

    $header = [
      ['data' => $this->t('Name')],
    ];

    $entity_list = [];
    foreach ($entities ?: [] as $entity) {
      /* @var \Drupal\cloud\Entity\CloudConfig $entity */
      if ($this->currentUser->hasPermission('view ' . $entity->getCloudContext()) || $this->currentUser->hasPermission('view all cloud service providers')) {
        $entity_list[] = [
          Link::createFromRoute($entity->getName(), $this->cloudConfigPluginManager->getServerTemplateCollectionName(), [
            'cloud_context' => $entity->getCloudContext(),
          ]),
        ];
      }
    }
    if (count($entity_list)) {
      $build['instances'][] = [
        '#markup' => $this->t('Launch an instance in one of these regions'),
      ];

      $build['instances'][] = [
        '#theme' => 'table',
        '#header' => $header,
        '#rows' => $entity_list,
      ];
    }
    else {
      $build['instances'][] = [
        '#markup' => $this->t('There are no regions configured. Cannot launch instances.'),
      ];
    }
    return $build;
  }

}
