<?php

namespace Drupal\gapps\Service;

use Drupal\cloud\Service\CloudServiceBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_Permission;
use Google_Service_Exception;
use Google_Service_Sheets;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_Request;
use Google_Service_Sheets_Spreadsheet;
use Google_Service_Sheets_ValueRange;

/**
 * The google spreadsheet service.
 */
class GoogleSpreadsheetService extends CloudServiceBase {

  /**
   * The config factory.
   *
   * Subclasses should use the self::config() method, which may be overridden to
   * address specific needs when loading config, rather than this property
   * directly. See \Drupal\Core\Form\ConfigFormBase::config() for an example of
   * this.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {

    // The parent constructor takes care of $this->messenger object.
    parent::__construct();

    $this->configFactory = $config_factory;
    // The parent constructor takes care of $this->messenger object.
    parent::__construct();
  }

  /**
   * Create or update a spreadsheet.
   *
   * @param string $spreadsheet_url
   *   The url of the spreadsheet.
   * @param string $region
   *   The name of an Amazon EC2 region.
   * @param string $title
   *   The title of the spreadsheet.
   * @param array $fields
   *   The price fields.
   * @param array $field_datas
   *   The price field datas.
   * @param array $field_widths
   *   The price field widths.
   *
   * @return string
   *   The url of the spreadsheet created.
   */
  public function createOrUpdate($spreadsheet_url, $region, $title, array $fields, array $field_datas, array $field_widths) {
    try {
      $spreadsheet_id = NULL;
      if (!empty($spreadsheet_url)) {
        if (preg_match('/spreadsheets\/d\/(.+)\/edit/', $spreadsheet_url, $matches)) {
          $spreadsheet_id = $matches[1];
        }
      }

      // Get the API client and construct the service object.
      $client = $this->getClient();
      $service = new Google_Service_Sheets($client);

      // Create spreadsheet if spreadsheet ID is NULL.
      if (empty($spreadsheet_id)) {
        $spreadsheet = $this->createSpreadsheet($client, $service, $title);
        $spreadsheet_id = $spreadsheet->getSpreadsheetId();
      }
      else {
        $spreadsheet = $service->spreadsheets->get($spreadsheet_id);
      }

      // Add or update the sheet for region.
      $sheet = $this->findSheet($service, $spreadsheet, $region);
      if ($sheet === NULL) {
        $sheet = $this->createSheet($service, $spreadsheet, $region);
      }

      $sheet_id = $sheet->getProperties()->getSheetId();
      $sheet_title = $sheet->getProperties()->getTitle();

      // Append data.
      $values = $this->getValues(
        $fields,
        $field_datas
      );
      $request_body = new Google_Service_Sheets_ValueRange(['values' => $values]);
      $service->spreadsheets_values->update(
        $spreadsheet_id,
        $sheet_title . '!A1',
        $request_body,
        ['valueInputOption' => 'USER_ENTERED']
      );

      $requests = [];
      $value_count = count($values[0]);
      for ($i = 1; $i < $value_count; $i++) {
        $my_range = [
          'sheetId' => $sheet_id,
          'startRowIndex' => 1,
          'endRowIndex' => count($values),
          'startColumnIndex' => $i,
          'endColumnIndex' => $i + 1,
        ];
        $requests[] = new Google_Service_Sheets_Request([
          'addConditionalFormatRule' => [
            'rule' => [
              'ranges' => [$my_range],
              'gradientRule' => [
                'minpoint' => [
                  'color' => [
                    'green' => 1,
                    'red' => 0,
                  ],
                  'type' => 'MIN',
                ],
                'midpoint' => [
                  // Color rgb(255, 214, 102).
                  'color' => [
                    'red' => 1,
                    'green' => 0.84,
                    'blue' => 0.4,
                  ],
                  'type' => 'PERCENTILE',
                  'value' => '50',
                ],
                'maxpoint' => [
                  'color' => [
                    'green' => 0,
                    'red' => 1,
                  ],
                  'type' => 'MAX',
                ],
              ],
            ],
            'index' => 0,
          ],
        ]);
      }

      // Make header align to center.
      $requests[] = new Google_Service_Sheets_Request([
        'repeatCell' => [
          'cell' => [
            'userEnteredFormat' => [
              'horizontalAlignment' => 'CENTER',
              'verticalAlignment' => 'MIDDLE',
            ],
          ],
          'range' => [
            'sheetId' => $sheet_id,
            'startRowIndex' => 0,
            'endRowIndex' => 1,
            'startColumnIndex' => 0,
            'endColumnIndex' => count($values[0]),
          ],
          'fields' => 'userEnteredFormat',
        ],
      ]);

      // Update the font family to Lato.
      $requests[] = new Google_Service_Sheets_Request([
        'repeatCell' => [
          'cell' => [
            'userEnteredFormat' => [
              'textFormat' => [
                'fontFamily' => 'Lato',
              ],
            ],
          ],
          'range' => [
            'sheetId' => $sheet_id,
            'startRowIndex' => 0,
            'endRowIndex' => count($values),
            'startColumnIndex' => 0,
            'endColumnIndex' => count($values[0]),
          ],
          'fields' => 'userEnteredFormat.textFormat.fontFamily',
        ],
      ]);

      // Get data by columns.
      $field_index = 0;
      $data_in_columns = array_fill(0, count($fields), []);
      foreach ($field_datas ?: [] as $field_data) {
        $column_index = 0;
        foreach ($field_data ?: [] as $item) {
          $data_in_columns[$column_index++][] = $item;
        }
      }

      // Update number format.
      $column_index = 0;
      foreach ($data_in_columns ?: [] as $data_in_column) {
        $max_decimal_digit_length = 0;
        foreach ($data_in_column ?: [] as $item) {
          if (!is_numeric($item)) {
            continue;
          }
          $item = floatval($item);
          $decimal_digit_length = strlen(substr(strrchr($item, "."), 1));
          if ($decimal_digit_length > $max_decimal_digit_length) {
            $max_decimal_digit_length = $decimal_digit_length;
          }
        }

        $pattern = '#,##0';
        if ($max_decimal_digit_length > 0) {
          $pattern .= '.' . str_repeat('0', $max_decimal_digit_length);
        }

        $requests[] = new Google_Service_Sheets_Request([
          'repeatCell' => [
            'cell' => [
              'userEnteredFormat' => [
                'numberFormat' => [
                  'type' => 'NUMBER',
                  'pattern' => $pattern,
                ],
              ],
            ],
            'range' => [
              'sheetId' => $sheet_id,
              'startRowIndex' => 0,
              'endRowIndex' => count($data_in_column) + 1,
              'startColumnIndex' => $column_index,
              'endColumnIndex' => ++$column_index,
            ],
            'fields' => 'userEnteredFormat.numberFormat',
          ],
        ]);
      }

      // Add basic filter.
      $requests[] = new Google_Service_Sheets_Request([
        'setBasicFilter' => [
          'filter' => [
            'range' => [
              'sheetId' => $sheet_id,
              'startRowIndex' => 0,
              'endRowIndex' => 1,
              'startColumnIndex' => 0,
              'endColumnIndex' => count($values[0]),
            ],
          ],
        ],
      ]);

      // Freeze column and row and update sheet name.
      $requests[] = new Google_Service_Sheets_Request([
        'updateSheetProperties' => [
          'properties' => [
            'sheetId' => $sheet_id,
            'gridProperties' => [
              'frozenRowCount' => 1,
              'frozenColumnCount' => 1,
            ],
            'title' => $region,
          ],
          'fields' => 'gridProperties.frozenRowCount,gridProperties.frozenColumnCount,title',
        ],
      ]);

      // Update columns width.
      $field_index = 0;
      foreach ($field_widths ?: [] as $field_width) {
        if (empty($field_width)) {
          $field_index++;
          continue;
        }

        $requests[] = new Google_Service_Sheets_Request([
          'updateDimensionProperties' => [
            'range' => [
              'sheetId' => $sheet_id,
              'dimension' => 'COLUMNS',
              'startIndex' => $field_index,
              'endIndex' => ++$field_index,
            ],
            'properties' => [
              'pixelSize' => $field_width,
            ],
            'fields' => 'pixelSize',
          ],
        ]);
      }

      $batch_update_request = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
        ['requests' => $requests]
      );
      $service->spreadsheets->batchUpdate($spreadsheet_id, $batch_update_request);

      return $spreadsheet->getSpreadsheetUrl() . '#gid=' . $sheet_id;
    }
    catch (Google_Service_Exception $e) {
      foreach ($e->getErrors() ?: [] as $error) {
        $this->messenger->addError($this->t('Failed to create spreadsheet due to the Google_Service_Exception with error "@message"', [
          '@message' => $error['message'],
        ]));
      }
    }
    catch (\Exception $e) {
      $this->messenger->addError($this->t('Failed to create spreadsheet due to the Exception with error "@message"', [
        '@message' => $e->getMessage(),
      ]));
    }

    return '';
  }

  /**
   * Delete a spreadsheet.
   *
   * @param string $spreadsheet_url
   *   The url of a spreadsheet.
   */
  public function delete($spreadsheet_url) {
    try {
      $client = $this->getClient();
      $drive_service = new Google_Service_Drive($client);

      if (preg_match('/spreadsheets\/d\/(.+)\/edit/', $spreadsheet_url, $matches)) {
        $spreadsheet_id = $matches[1];
        $drive_service->files->delete($spreadsheet_id);
      }
    }
    catch (Google_Service_Exception $e) {
      foreach ($e->getErrors() ?: [] as $error) {
        $this->messenger->addError($this->t('Failed to delete spreadsheet due to the Google_Service_Exception with error "@message"', [
          '@message' => $error['message'],
        ]));
      }
    }
    catch (\Exception $e) {
      $this->messenger->addError($this->t('Failed to delete spreadsheet due to the Exception with error "@message"', [
        '@message' => $e->getMessage(),
      ]));
    }
  }

  /**
   * Get client.
   *
   * @return \Google_Client
   *   Google client.
   */
  private function getClient() {
    $client = new Google_Client();
    $client->setApplicationName('Cloud');
    $client->setScopes([
      Google_Service_Sheets::SPREADSHEETS,
      Google_Service_Drive::DRIVE,
    ]);

    $client->setAuthConfig(gapps_google_credential_file_path());
    $client->setAccessType('offline');
    return $client;
  }

  /**
   * Get values for spreadsheet.
   *
   * @param array $fields
   *   Fields.
   * @param array $data
   *   Data.
   *
   * @return array
   *   The values for spreadsheet.
   */
  private function getValues(array $fields, array $data) {
    $headers = array_map(static function ($item) {
      return str_replace('<br>', "\n", $item->render());
    }, array_values($fields));

    $rows = array_map(static function ($item) {
      return array_values($item);
    }, $data);

    return array_merge([$headers], array_values($rows));
  }

  /**
   * Create a spreadsheet and set the permission.
   *
   * @param \Google_Client $client
   *   The google API client.
   * @param \Google_Service_Sheets $service
   *   The google spreadsheet service.
   * @param string $title
   *   The title of the spreadsheet.
   *
   * @return \Google_Service_Sheets_Spreadsheet
   *   The new spreadsheet created.
   */
  private function createSpreadsheet(
    Google_Client $client,
    Google_Service_Sheets $service,
    $title
  ) {
    $spreadsheet = new Google_Service_Sheets_Spreadsheet([
      'properties' => [
        'title' => $title,
      ],
    ]);
    $spreadsheet = $service->spreadsheets->create($spreadsheet);
    $spreadsheet_id = $spreadsheet->getSpreadsheetId();

    // Share the file.
    $drive_service = new Google_Service_Drive($client);
    $userPermission = new Google_Service_Drive_Permission([
      'type' => 'anyone',
      'role' => 'reader',
    ]);
    $drive_service->permissions->create(
      $spreadsheet_id,
      $userPermission,
      ['fields' => 'id']
    );

    return $spreadsheet;
  }

  /**
   * Find the sheet with the same name as the region's name.
   *
   * @param \Google_Service_Sheets $service
   *   The google spreadsheet service.
   * @param \Google_Service_Sheets_Spreadsheet $spreadsheet
   *   The spreadsheet.
   * @param string $region
   *   The region.
   *
   * @return \Google_Service_Sheets_Sheet
   *   The sheet found. NULL if there is no sheet found.
   */
  private function findSheet(
    Google_Service_Sheets $service,
    Google_Service_Sheets_Spreadsheet $spreadsheet,
    $region
  ) {
    $spreadsheet_id = $spreadsheet->getSpreadsheetId();
    $sheets = $spreadsheet->getSheets();
    foreach ($sheets ?: [] as $sheet) {
      if ($sheet->getProperties()->getTitle() === $region) {
        return $sheet;
      }
    }

    // If there is only a default sheet, use it and update title.
    if (count($sheets) === 1 && $sheets[0]->getProperties()->getTitle() === 'Sheet1') {
      $request = new Google_Service_Sheets_Request([
        'updateSheetProperties' => [
          'properties' => [
            'sheetId' => 0,
            'title' => $region,
          ],
          'fields' => 'title',
        ],
      ]);

      $batch_update_request = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
        ['requests' => [$request]]
      );
      $service->spreadsheets->batchUpdate($spreadsheet_id, $batch_update_request);
      $sheets[0]->getProperties()->setTitle($region);
      return $sheets[0];
    }

    return NULL;
  }

  /**
   * Find the place to insert the sheet of the region.
   *
   * @param \Google_Service_Sheets_Spreadsheet $spreadsheet
   *   The spreadsheet.
   * @param string $region
   *   The region.
   *
   * @return int
   *   The place to insert.
   */
  private function findSheetInsertIndex(
    Google_Service_Sheets_Spreadsheet $spreadsheet,
    $region
  ) {
    $sheets = $spreadsheet->getSheets();
    foreach ($sheets ?: [] as $sheet) {
      if ($sheet->getProperties()->getTitle() > $region) {
        return $sheet->getProperties()->getIndex();
      }
    }

    return $sheets[count($sheets) - 1]->getProperties()->getIndex() + 1;
  }

  /**
   * Create a sheet.
   *
   * @param \Google_Service_Sheets $service
   *   The google spreadsheet service.
   * @param \Google_Service_Sheets_Spreadsheet $spreadsheet
   *   The spreadsheet.
   * @param string $region
   *   The region.
   *
   * @return \Google_Service_Sheets_Sheet
   *   The sheet created.
   */
  private function createSheet(
    Google_Service_Sheets $service,
    Google_Service_Sheets_Spreadsheet $spreadsheet,
    $region
  ) {
    $spreadsheet_id = $spreadsheet->getSpreadsheetId();

    // Create a sheet.
    $request = new Google_Service_Sheets_Request([
      'addSheet' => [
        'properties' => [
          'title' => $region,
          'index' => $this->findSheetInsertIndex($spreadsheet, $region),
        ],
      ],
    ]);

    $batch_update_request = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
      ['requests' => [$request]]
    );
    $response = $service->spreadsheets->batchUpdate($spreadsheet_id, $batch_update_request);
    return $response->getReplies()[0]->getAddSheet();
  }

}
