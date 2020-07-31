<?php

namespace Drupal\cloud_budget\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an InPlaceEditor annotation object.
 *
 * @see \Drupal\cloud_budget\Plugin\cloud_budget\CloudCostCalculatorBase
 * @see \Drupal\cloud_budget\Plugin\cloud_budget\CloudCostCalculatorInterface
 * @see \Drupal\cloud_budget\Plugin\cloud_budget\CloudCostCalculatorManager
 * @see plugin_api
 *
 * @Annotation
 */
class CloudCostCalculator extends Plugin {

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
