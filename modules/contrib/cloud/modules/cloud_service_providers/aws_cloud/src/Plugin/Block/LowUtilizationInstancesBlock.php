<?php

namespace Drupal\aws_cloud\Plugin\Block;

use Drupal\aws_cloud\Service\CloudWatch\LowUtilizationInstanceChecker;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block displaying low utilization instances.
 *
 * @Block(
 *   id = "aws_cloud_low_utilization_instances_block",
 *   admin_label = @Translation("Low Utilization Instances"),
 *   category = @Translation("AWS Cloud")
 * )
 */
class LowUtilizationInstancesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Low utilization instance checker.
   *
   * @var \Drupal\aws_cloud\CloudWatch\Service\LowUtilizationInstanceChecker
   */
  protected $lowUtilizationInstanceChecker;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Creates a LongRunningInstancesBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   An entity type manager instance.
   * @param \Drupal\aws_cloud\CloudWatch\Service\LowUtilizationInstanceChecker $low_utilization_instance_checker
   *   The low utilization instance checker.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    LowUtilizationInstanceChecker $low_utilization_instance_checker,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->lowUtilizationInstanceChecker = $low_utilization_instance_checker;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('aws_cloud.low_utilization_instances_checker'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return $this->buildInstanceList();
  }

  /**
   * Build a list of low utilization instances.
   *
   * @return array
   *   Array of instance URLs.
   */
  private function buildInstanceList() {
    $build = [];
    $build['instances'] = [
      '#type' => 'details',
      '#title' => $this->t('Low Utilization Instances'),
      '#open' => TRUE,
    ];

    $instances = $this->getLowUtilizationInstances();
    if (empty($instances)) {
      $build['instances'][] = [
        '#markup' => $this->t('Great job! You have no low utilization instances.'),
      ];
      return $build;
    }

    $urls = array_map(function ($instance) {
      return $instance->toLink($this->t('@instance', [
        '@instance' => $instance->getName(),
      ]));
    }, $instances);

    $cpu_threshold = $this->configFactory
      ->get('aws_cloud.settings')
      ->get('aws_cloud_low_utilization_instance_cpu_utilization_threshold');

    $network_threshold = $this->configFactory
      ->get('aws_cloud.settings')
      ->get('aws_cloud_low_utilization_instance_network_io_threshold');

    $period = $this->configFactory
      ->get('aws_cloud.settings')
      ->get('aws_cloud_low_utilization_instance_period');

    $build['instances'][] = [
      '#markup' => $this->t('The following instances are low utilization instances, whose daily CPU utilization was @cpu_threshold% or less and network I/O was @network_threshold MB or less in last @period days',
        [
          '@cpu_threshold' => $cpu_threshold,
          '@network_threshold' => $network_threshold,
          '@period' => $period,
        ]
      ),
    ];
    $build['instances'][] = [
      '#theme' => 'item_list',
      '#items' => $urls,
    ];

    return $build;
  }

  /**
   * Get a list of low utilization instances.
   *
   * @return array
   *   Array of low utilization instances.
   */
  private function getLowUtilizationInstances() {
    $low_instances = [];
    $instances = $this->entityTypeManager
      ->getStorage('aws_cloud_instance')
      ->loadByProperties(['instance_state' => 'running']);
    foreach ($instances ?? [] as $instance) {
      if ($this->lowUtilizationInstanceChecker->isLow(
        $instance->getCloudContext(),
        $instance->getInstanceId()
      )) {
        $low_instances[] = $instance;
      }
    }

    return $low_instances;
  }

}
