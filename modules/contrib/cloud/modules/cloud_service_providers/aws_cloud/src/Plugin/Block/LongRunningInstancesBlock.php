<?php

namespace Drupal\aws_cloud\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block displaying long running instances.
 *
 * @Block(
 *   id = "aws_cloud_long_running_instances_block",
 *   admin_label = @Translation("Long Running Instances"),
 *   category = @Translation("AWS Cloud")
 * )
 */
class LongRunningInstancesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Creates a LongRunningInstancesBlock instance.
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
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    AccountInterface $current_user
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
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
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return $this->buildInstanceList();
  }

  /**
   * Build a list of long running instances.
   *
   * @return array
   *   Array of instance URLs.
   */
  private function buildInstanceList() {
    $build = [];
    $build['instances'] = [
      '#type' => 'details',
      '#title' => $this->t('Long Running Instances'),
      '#open' => TRUE,
    ];

    $header = [
      ['data' => $this->t('Name')],
    ];

    $instances = $this->getLongRunningInstances();
    if (count($instances)) {
      $urls = [];
      foreach ($instances ?: [] as $instance) {
        $days_running = $instance->daysRunning();
        $link_text = $this->t('@instance (%running @days)', [
          '@instance' => $instance->getName(),
          '%running' => $days_running,
          '@days' => $this->formatPlural($days_running, 'day', 'days'),
        ]);
        $urls[] = [$instance->toLink($link_text)];
      }
      $unused_days = $this->configFactory->get('aws_cloud.settings')->get('aws_cloud_long_running_instance_notification_criteria');
      $build['instances'][] = [
        '#markup' => $this->t('The following instances have been running for more than %num days', ['%num' => $unused_days]),
      ];

      $build['instances'][] = [
        '#theme' => 'table',
        '#header' => $header,
        '#rows' => $urls,
      ];
    }
    else {
      $build['instances'][] = [
        '#markup' => $this->t('Great job! You have no long running instances.'),
      ];
    }
    return $build;
  }

  /**
   * Get a list of long running instances.
   *
   * @return array
   *   Array of long running instances.
   */
  private function getLongRunningInstances() {
    $instances = aws_cloud_get_long_running_instances();
    if (!$this->currentUser->hasPermission('view any aws cloud instance')) {
      foreach ($instances ?: [] as $key => $instance) {
        if ($instance->getOwnerId() !== $this->currentUser->id()) {
          unset($instances[$key]);
        }
      }
    }
    return $instances;
  }

}
