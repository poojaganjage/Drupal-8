<?php

namespace Drupal\cloud_budget\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an Cloud Cost Storage annotation object.
 *
 * @see \Drupal\cloud_budget\Plugin\cloud_budget\CloudCostStorageBase
 * @see \Drupal\cloud_budget\Plugin\cloud_budget\CloudCostStorageInterface
 * @see \Drupal\cloud_budget\Plugin\cloud_budget\CloudCostStorageManager
 * @see plugin_api
 *
 * @Annotation
 */
class CloudCostStorage extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The name of the module providing the plugin.
   *
   * @var string
   */
  public $module;

}
