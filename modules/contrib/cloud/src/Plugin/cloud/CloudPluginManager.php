<?php

namespace Drupal\cloud\Plugin\cloud;

use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides a base class for Cloud plugin.
 */
class CloudPluginManager extends DefaultPluginManager implements CloudPluginManagerInterface {

  use CloudContentEntityTrait;

  /**
   * Creates the discovery object.
   *
   * @param string|bool $subdir
   *   The plugin's subdirectory, for example Plugin/views/filter.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param string|null $plugin_interface
   *   (optional) The interface each plugin should implement.
   * @param string $plugin_definition_annotation_name
   *   (optional) The name of the annotation containing the plugin definition.
   *   Defaults to 'Drupal\Component\Annotation\Plugin'.
   * @param string[] $additional_annotation_namespaces
   *   (optional) Additional namespaces to scan for annotation definitions.
   */
  public function __construct($subdir,
                              \Traversable $namespaces,
                              ModuleHandlerInterface $module_handler,
                              $plugin_interface = NULL,
                              $plugin_definition_annotation_name = 'Drupal\Component\Annotation\Plugin',
                              array $additional_annotation_namespaces = []) {

    parent::__construct($subdir,
                        $namespaces,
                        $module_handler,
                        $plugin_interface,
                        $plugin_definition_annotation_name,
                        $additional_annotation_namespaces
    );

    $this->messenger();
  }

}
