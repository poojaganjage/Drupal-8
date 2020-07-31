<?php

namespace Drupal\gapps\Plugin\gapps;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for google spreadsheet updater plugins.
 *
 * @see \Drupal\gapps\Annotation\GoogleSpreadsheetUpdater
 * @see \Drupal\gapps\Plugin\gapps\GoogleSpreadsheetUpdaterBase
 * @see \Drupal\gapps\Plugin\gapps\GoogleSpreadsheetUpdaterManager
 * @see plugin_api
 */
interface GoogleSpreadsheetUpdaterInterface extends PluginInspectionInterface {

  /**
   * Delete google spreadsheets.
   *
   * @return array
   *   The cloud configs changed.
   */
  public function deleteSpreadsheets();

}
