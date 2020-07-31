<?php

namespace Drupal\gapps\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an InPlaceEditor annotation object.
 *
 * @see \Drupal\gapps\Plugin\gapps\GoogleSpreadsheetUpdaterBase
 * @see \Drupal\gapps\Plugin\gapps\GoogleSpreadsheetUpdaterInterface
 * @see \Drupal\gapps\Plugin\gapps\GoogleSpreadsheetUpdaterManager
 * @see plugin_api
 *
 * @Annotation
 */
class GoogleSpreadsheetUpdater extends Plugin {

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
