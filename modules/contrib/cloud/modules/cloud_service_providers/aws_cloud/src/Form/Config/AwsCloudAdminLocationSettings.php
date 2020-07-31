<?php

namespace Drupal\aws_cloud\Form\Config;

use Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Locale\CountryManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AWS Cloud Admin Location Settings.
 */
class AwsCloudAdminLocationSettings extends ConfigFormBase {

  /**
   * The Amazon EC2 Service.
   *
   * @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface
   */
  private $ec2Service;

  /**
   * The country manager.
   *
   * @var \Drupal\Core\Locale\CountryManagerInterface
   */
  private $countryManager;

  /**
   * Constructs a AwsCloudAdminSettings object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface $ec2_service
   *   The ec2 manager.
   * @param \Drupal\Core\Locale\CountryManagerInterface $country_manager
   *   Country Manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    Ec2ServiceInterface $ec2_service,
    CountryManagerInterface $country_manager
  ) {
    parent::__construct($config_factory);
    $this->ec2Service = $ec2_service;
    $this->countryManager = $country_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('aws_cloud.ec2'),
      $container->get('country_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'aws_cloud_admin_location_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['aws_cloud.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('aws_cloud.settings');

    $location = $config->get('aws_cloud_region_locations');
    $country_options = $this->countryManager->getStandardList();
    $region = $this->ec2Service->getRegions();

    $form['location'] = [
      '#type' => 'details',
      '#title' => $this->t('Location of Regions'),
      '#description' => $this->t('Define the location for AWS regions.'),
      '#open' => TRUE,
    ];

    $header = [
      $this->t('Region'),
      $this->t('Country'),
      $this->t('City'),
      $this->t('Latitude'),
      $this->t('Longitude'),
    ];

    $form['location']['aws_cloud_region_locations'] = [
      '#type' => 'table',
      '#header' => $header,
      '#header_columns' => 5,
    ];

    foreach ($region ?: [] as $key => $value) {
      $form['location']['aws_cloud_region_locations'][$key] = [
        ['data' => ['#markup' => "<span class='region'>$value</span>"]],
        'country' => [
          '#type' => 'select',
          '#options' => $country_options,
          '#default_value' => $location[$key]['country'],
          '#attributes' => ['autocomplete' => 'off'],
        ],
        'city' => [
          '#type' => 'textfield',
          '#size' => 30,
          '#default_value' => $location[$key]['city'],
        ],
        'latitude' => [
          '#type' => 'number',
          '#default_value' => $location[$key]['latitude'],
          '#max' => '90',
          '#min' => '-90',
          '#scale' => 6,
          '#step' => 'any',
        ],
        'longitude' => [
          '#type' => 'number',
          '#default_value' => $location[$key]['longitude'],
          '#max' => '180',
          '#min' => '-180',
          '#scale' => 6,
          '#step' => 'any',
        ],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable('aws_cloud.settings');

    $form_state->cleanValues();
    $values = $form_state->getValues();
    $config->set('aws_cloud_region_locations', $values['aws_cloud_region_locations']);
    $config->save();

    parent::submitForm($form, $form_state);

  }

}
