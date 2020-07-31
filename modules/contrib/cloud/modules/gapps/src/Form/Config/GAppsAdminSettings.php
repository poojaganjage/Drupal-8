<?php

namespace Drupal\gapps\Form\Config;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\gapps\Plugin\gapps\GoogleSpreadsheetUpdaterManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GApps Admin Settings.
 */
class GAppsAdminSettings extends ConfigFormBase {

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * The google spreadsheet updater manager.
   *
   * @var \Drupal\gapps\Plugin\gapps\GoogleSpreadsheetUpdaterManager
   */
  private $googleSpreadsheetUpdaterManager;

  /**
   * Constructs a AwsCloudAdminSettings object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\gapps\Plugin\gapps\GoogleSpreadsheetUpdaterManager $google_spreadsheet_updater_manager
   *   The google spreadsheet updater manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    FileSystemInterface $file_system,
    GoogleSpreadsheetUpdaterManager $google_spreadsheet_updater_manager
  ) {
    parent::__construct($config_factory);

    $this->fileSystem = $file_system;
    $this->googleSpreadsheetUpdaterManager = $google_spreadsheet_updater_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('file_system'),
      $container->get('plugin.manager.google_spreadsheet_updater')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gapps_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['gapps.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('gapps.settings');
    $form['google'] = [
      '#type' => 'details',
      '#title' => $this->t('Google'),
      '#open' => TRUE,
    ];

    $form['google']['google_credential'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Google Credential'),
      '#description' => $this->t("The credential data of a service account."),
      '#rows' => 15,
    ];

    $form['google']['google_credential_file_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google Credential File Path'),
      '#description' => $this->t(
        "The path of a service account's credential file. The default path is @path.",
        ['@path' => gapps_google_credential_file_default_path()]
      ),
      '#default_value' => gapps_google_credential_file_path(),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $google_credential = $form_state->getValue('google_credential');
    if (empty($google_credential)) {
      $credential_file_path = $form_state->getValue('google_credential_file_path');
      if (empty($credential_file_path)) {
        $credential_file_path = gapps_google_credential_file_default_path();
      }
      $config_credential_file_path = $this->config('gapps.settings')->get('google_credential_file_path');
      if (empty($config_credential_file_path)) {
        $config_credential_file_path = gapps_google_credential_file_default_path();
      }

      if ($config_credential_file_path === $credential_file_path
        && file_exists($config_credential_file_path)
      ) {
        return;
      }

      $form_state->setErrorByName(
        'google_credential',
        $this->t('The google credential is empty.')
      );
      return;
    }

    if (json_decode($google_credential) === NULL) {
      $form_state->setErrorByName(
        'google_credential',
        $this->t('The google credential is not valid json format.')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable('gapps.settings');
    $form_state->cleanValues();

    $old_credential_file_path = $config->get('google_credential_file_path');
    $google_credential_file_path = trim($form_state->getValue('google_credential_file_path'));

    // If 'google_credential_file_path' is specified, store the
    // signature of the JSON file at 'google_credential_file_path'.
    // If 'google_credential' w/ the credential value is specified,
    // use the signature of the 'google_credential'.
    $google_credential = trim($form_state->getValue('google_credential'));
    if (empty($google_credential)) {
      $value = file_get_contents($google_credential_file_path);

      if (!empty($value)) {
        $config->set(
          'google_credential_signature',
          hash('sha256', json_encode(json_decode($value)))
        );
      }
    }

    $config->set('google_credential_file_path', $google_credential_file_path);
    $config->save();

    if (!empty($google_credential)) {
      $this->saveGoogleCredential(
        $config,
        $google_credential,
        $old_credential_file_path
      );
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * Save google credential to file.
   *
   * @param \Drupal\Core\Config\Config $config
   *   The config object.
   * @param string $credential
   *   The credential data.
   * @param string $old_credential_file_path
   *   The old credential file path.
   */
  private function saveGoogleCredential(Config $config, $credential, $old_credential_file_path) {
    $credential_file_path = gapps_google_credential_file_path();
    $credential_dir = $this->fileSystem->dirname($credential_file_path);
    if ($this->fileSystem->prepareDirectory(
      $credential_dir,
      FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS
    )) {
      // If credential changed, the old google spreadsheets should be deleted.
      $cloud_configs_changed = [];
      if ($this->isGoogleCredentialChanged($credential_file_path, $credential)) {
        $cloud_configs_changed = $this->googleSpreadsheetUpdaterManager->deleteAllSpreadsheets();
      }

      $this->fileSystem->saveData($credential, $credential_file_path, FileSystemInterface::EXISTS_REPLACE);

      // Save credential signature.
      $config->set(
        'google_credential_signature',
        hash('sha256', json_encode(json_decode($credential, TRUE)))
      );
      $config->save();

      if (empty($old_credential_file_path)) {
        $old_credential_file_path = gapps_google_credential_file_default_path();
      }

      // Remove old file.
      if ($old_credential_file_path !== $credential_file_path
        && file_exists($old_credential_file_path)
      ) {
        $this->fileSystem->delete($old_credential_file_path);
      }

      // Save cloud service providers (CloudConfig) changed.
      // The spreadsheets belonging to them will updated by hook function.
      foreach ($cloud_configs_changed ?: [] as $cloud_config) {
        $cloud_config->save();
      }
    }
  }

  /**
   * Check whether the google credential changed.
   *
   * @param string $credential_file_path
   *   The file path of google credential.
   * @param string $new_credential
   *   The new google credential content.
   *
   * @return bool
   *   Whether the google credential changed or not.
   */
  private function isGoogleCredentialChanged($credential_file_path, $new_credential) {
    if (!file_exists($credential_file_path)) {
      return TRUE;
    }

    $old_credential = file_get_contents($credential_file_path);
    if ($old_credential === FALSE) {
      return TRUE;
    }

    return trim($old_credential) !== trim($new_credential);
  }

}
