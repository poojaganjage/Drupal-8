<?php

namespace Drupal\cloud\Plugin\views\field;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to present a link to an external pricing.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("cloud_pricing_external")
 */
class CloudPricingExternalLink extends FieldPluginBase {

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * Constructs a LinkBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Access\AccessManagerInterface $access_manager
   *   The access manager.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccessManagerInterface $access_manager, CloudConfigPluginManagerInterface $cloud_config_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $access_manager);
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
      $container->get('access_manager'),
      $container->get('plugin.manager.cloud_config_plugin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $row) {
    $output = [];
    if ($row->_entity->hasField('field_spreadsheet_pricing_url')) {
      $uri = $row->_entity->get('field_spreadsheet_pricing_url')->value;
      if (!is_null($uri)) {
        $output = [
          '#title' => 'view',
          '#type' => 'link',
          '#url' => Url::fromUri($uri),
          '#attributes' => ['target' => '_blank'],
        ];
      }
    }
    return $output;
  }

}
