<?php

namespace Drupal\openstack\Form\Config;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class OpenStack Admin Settings.
 */
class OpenStackAdminSettings extends ConfigFormBase {

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * Constructs a OpenStackAdminSettings object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    FileSystemInterface $file_system
  ) {
    parent::__construct($config_factory);

    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('file_system')
    );
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'openstack_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['openstack.settings'];
  }

  /**
   * Defines the settings form for OpenStack.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('openstack.settings');

    $form['test_mode'] = [
      '#type' => 'details',
      '#title' => $this->t('Test Mode'),
      '#open' => TRUE,
    ];

    $form['test_mode']['openstack_test_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable test mode?'),
      '#default_value' => $config->get('openstack_test_mode'),
      '#description' => $this->t('This enables you to test the OpenStack module settings without accessing OpenStack.'),
    ];

    $form['icon'] = [
      '#type' => 'details',
      '#title' => $this->t('Icon'),
      '#open' => TRUE,
    ];

    $form['icon']['openstack_cloud_config_icon'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('OpenStack Cloud Config Icon'),
      '#default_value' => [
        'fids' => $config->get('openstack_cloud_config_icon'),
      ],
      '#description' => $this->t('Upload the default image to represent OpenStack.'),
      '#upload_location' => 'public://images/icons',
      '#upload_validators' => [
        'file_validate_is_image' => [],
      ],
    ];

    $form['volume'] = [
      '#type' => 'details',
      '#title' => $this->t('Volume'),
      '#open' => TRUE,
    ];

    $form['volume']['notification_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Notification Settings'),
      '#open' => TRUE,
    ];

    $form['volume']['notification_settings']['openstack_unused_volume_criteria'] = [
      '#type' => 'select',
      '#title' => $this->t('Unused volume criteria'),
      '#description' => $this->t('A volume is considered unused if it has been created and available for the specified number of days.'),
      '#options' => [
        30 => $this->t('30 days'),
        60 => $this->t('60 days'),
        90 => $this->t('90 days'),
        180 => $this->t('180 days'),
        365 => $this->t('One year'),
      ],
      '#default_value' => $config->get('openstack_unused_volume_criteria'),
    ];

    $form['schedule'] = [
      '#type' => 'details',
      '#title' => $this->t('Schedule'),
      '#open' => TRUE,
    ];

    $form['schedule']['termination_options'] = [
      '#type' => 'details',
      '#title' => $this->t('Termination Options'),
      '#open' => TRUE,
    ];

    $form['schedule']['termination_options']['openstack_instance_terminate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Automatically terminate instance'),
      '#description' => $this->t('Terminate instance automatically.'),
      '#default_value' => $config->get('openstack_instance_terminate'),
    ];

    $form['cron'] = [
      '#type' => 'details',
      '#title' => $this->t('Cron'),
      '#open' => TRUE,
    ];

    $form['cron']['openstack_update_resources_queue_cron_time'] = [
      '#type' => 'number',
      '#title' => 'Update Resources Queue Cron Time',
      '#description' => $this->t('The cron time for queue update resources.'),
      '#default_value' => $config->get('openstack_update_resources_queue_cron_time'),
      '#min' => 1,
      '#max' => 9999,
      '#field_suffix' => 'seconds',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable('openstack.settings');
    $form_state->cleanValues();

    foreach ($form_state->getValues() ?: [] as $key => $value) {
      if ($key === 'openstack_cloud_config_icon') {
        $icon = $form_state->getValue('openstack_cloud_config_icon');
        $file = File::load($icon[0]);
        // Save the icon.
        if (!empty($file)) {
          $file->setPermanent();
          $file->save();
          $config->set('openstack_cloud_config_icon', $icon[0]);
        }
        else {
          $config->set('openstack_cloud_config_icon', '');
        }
        continue;
      }
      $config->set($key, Html::escape($value));
    }
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
