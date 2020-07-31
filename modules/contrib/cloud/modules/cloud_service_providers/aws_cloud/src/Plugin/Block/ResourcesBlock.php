<?php

namespace Drupal\aws_cloud\Plugin\Block;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Traits\ResourceBlockTrait;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block displaying system resources.
 *
 * @Block(
 *   id = "aws_cloud_resources_block",
 *   admin_label = @Translation("AWS Resources"),
 *   category = @Translation("AWS Cloud")
 * )
 */
class ResourcesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use ResourceBlockTrait;

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
   * The cloud config plugin manager.
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
   *   The cloud config plugin manager.
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
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['cloud_context'] = [
      '#type' => 'select',
      '#title' => $this->t('Cloud Service Provider'),
      '#description' => $this->t('Select cloud service provider.'),
      '#options' => $this->getCloudConfigs($this->t('All AWS Cloud regions'), 'aws_cloud'),
      '#default_value' => $config['cloud_context'] ?? '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['cloud_context'] = $form_state->getValue('cloud_context');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'cloud_context' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $cloud_configs = $this->getCloudConfigs($this->t('All AWS Cloud regions'), 'aws_cloud');
    $cloud_context = $this->configuration['cloud_context'];
    $cloud_context_name = empty($cloud_context)
      ? 'All AWS Cloud regions'
      : $cloud_configs[$cloud_context];
    $build = [];
    $build['resources'] = [
      '#type' => 'details',
      '#title' => $this->t('Resources'),
      '#open' => TRUE,
    ];

    $build['resources']['description'] = [
      '#markup' => $this->t('You are using the following AWS resources in %cloud_context_name:',
        ['%cloud_context_name' => $cloud_context_name]
      ),
    ];
    $build['resources']['resource_table'] = $this->buildResourceTable();

    return $build;
  }

  /**
   * Build a resource HTML table.
   *
   * @return array
   *   Table render array.
   */
  private function buildResourceTable() {
    $resources = [
      'aws_cloud_instance' => [
        'view any aws cloud instance',
        ['instance_state' => 'running'],
      ],
      'aws_cloud_image' => [
        'view any aws cloud image',
        [],
      ],
      'aws_cloud_security_group' => [
        'view any aws cloud security group',
        [],
      ],
      'aws_cloud_elastic_ip' => [
        'view any aws cloud elastic ip',
        [],
      ],
      'aws_cloud_network_interface' => [
        'view any aws cloud network interface',
        [],
      ],
      'aws_cloud_key_pair' => [
        'view any aws cloud key pair',
        [],
      ],
      'aws_cloud_volume' => [
        'view any aws cloud volume',
        [],
      ],
      'aws_cloud_snapshot' => [
        'view any aws cloud snapshot',
        [],
      ],
      'aws_cloud_vpc' => [
        'view any aws cloud vpc',
        [],
      ],
      'aws_cloud_subnet' => [
        'view any aws cloud subnet',
        [],
      ],
      'instance_type_prices' => [
        'Instance Type Prices',
      ],
    ];

    $rows = $this->buildResourceTableRows($resources);

    return [
      '#type' => 'table',
      '#rows' => $rows,
    ];
  }

  /**
   * Generate AWS Instance Type Pricing link.
   *
   * @param string $resource_type
   *   The resource type.
   * @param string $label
   *   The link lable.
   *
   * @return array
   *   The AWS resource link.
   */
  private function getInstanceTypePricingLink($resource_type, $label) {

    // Return immediately if $cloud_context is empty.
    $cloud_context = $this->configuration['cloud_context'];
    if (empty($cloud_context)) {
      return [];
    }
    $instance = count(aws_cloud_get_instance_types($cloud_context));
    $pricing_label = "$instance $label";
    $link = Link::createFromRoute($pricing_label, "aws_cloud.${resource_type}",
      ['cloud_context' => $cloud_context]
    );

    return $link;
  }

}
