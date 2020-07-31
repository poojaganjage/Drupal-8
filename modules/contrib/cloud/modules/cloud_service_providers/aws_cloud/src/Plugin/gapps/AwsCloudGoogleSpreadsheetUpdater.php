<?php

namespace Drupal\aws_cloud\Plugin\gapps;

use Drupal\gapps\Plugin\gapps\GoogleSpreadsheetUpdaterBase;

/**
 * Defines the google spreadsheet updater.
 *
 * @GoogleSpreadsheetUpdater(
 *   id = "aws_cloud_google_spreadsheet_updater"
 * )
 */
class AwsCloudGoogleSpreadsheetUpdater extends GoogleSpreadsheetUpdaterBase {

  /**
   * {@inheritdoc}
   */
  public function deleteSpreadsheets() {
    $config = $this->configFactory->get('aws_cloud.settings');
    if (empty($config->get('aws_cloud_instance_type_prices_spreadsheet'))) {
      return;
    }

    $cloud_configs = $this->cloudConfigPluginManager->loadConfigEntities('aws_cloud');
    $cloud_configs_changed = [];
    foreach ($cloud_configs ?: [] as $cloud_config) {
      $old_url = $cloud_config->get('field_spreadsheet_pricing_url')->value;
      if (!empty($old_url)) {
        $this->googleSpreadsheetService->delete($old_url);
        $cloud_config->set('field_spreadsheet_pricing_url', '');
        $cloud_configs_changed[] = $cloud_config;
      }
    }

    return $cloud_configs_changed;
  }

}
