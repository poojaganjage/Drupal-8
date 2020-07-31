<?php

namespace Drupal\cloud\Plugin\cloud;

use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Component\Plugin\PluginBase;

/**
 * Provides a base class for Cloud plugin.
 */
class CloudPluginBase extends PluginBase implements CloudPluginBaseInterface {

  use CloudContentEntityTrait;

  /**
   * AwsCloudServerTemplatePlugin constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->messenger();
  }

}
