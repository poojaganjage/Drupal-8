<?php

namespace Drupal\docker\Form\Config;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\docker\Service\DockerServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Docker Admin settings.
 */
class DockerAdminSettings extends ConfigFormBase {

  /**
   * Docker service.
   *
   * @var \Drupal\docker\Service\DockerServiceInterface
   */
  protected $docker;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\docker\Service\DockerServiceInterface $docker
   *   The docker service object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, DockerServiceInterface $docker) {
    parent::__construct($config_factory);
    $this->docker = $docker;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('docker')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'docker_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['docker.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('docker.settings');

    $form['docker']['local_docker'] = [
      '#type' => 'details',
      '#title' => $this->t('Local Docker'),
      '#open' => TRUE,
    ];

    $form['docker']['local_docker']['docker_unix_socket'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Unix Socket'),
      '#description' => $this->t('Local docker unix socket.  Docker must be
      installed on the Cloud Orchestrator server.  For example: /var/run/docker.sock'),
      '#required' => TRUE,
      '#default_value' => $config->get('docker_unix_socket'),
    ];

    $form['docker']['local_docker']['docker_api_version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Version'),
      '#description' => $this->t('Docker remote api version.  For example, 1.39.
      Leave blank to use the latest version.'),
      '#default_value' => $config->get('docker_api_version'),
    ];

    $form['docker']['local_docker']['description'] = [
      '#type' => 'markup',
      '#markup' => '<strong>' . $this->t('NOTE:') . '</strong>' . $this->t('
      In order for Drupal to access the Docker Remote API, the web server user
      (ex: www-data) must be part of the Docker unix group or has sudo access.
      For example, if the web server is on Ubuntu and the web server user is
      www-data, run this command: `sudo usermod -a -G docker www-data`.
      Make sure to restart the web server.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($this->docker->isDockerUp(
        $form_state->getValue('docker_unix_socket'),
        $form_state->getValue('docker_api_version')) === FALSE
    ) {
      // Set an error if docker is unreachable.
      $form_state->setErrorByName('docker_unix_socket', $this->t('Docker
      unreachable.  Please check unix socket and api version.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable('docker.settings');
    $config->set('docker_unix_socket', $form_state->getValue('docker_unix_socket'));
    $config->set('docker_api_version', $form_state->getValue('docker_api_version'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
