<?php

namespace Drupal\matomo_reports\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\UserDataInterface;
use Drupal\Core\Messenger\MessengerInterface;
use \Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class ReportsForm.
 */
class ReportsForm extends FormBase {

  /**
   * Drupal\user\UserDataInterface definition.
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new ReportsForm object.
   *
   * @param \Drupal\user\UserDataInterface $user_data
   *   The user data service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(UserDataInterface $user_data, MessengerInterface $messenger, ConfigFactoryInterface $configFactory) {
    $this->userData = $user_data;
    $this->messenger = $messenger;
    $this->configFactory = $configFactory;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.data'),
      $container->get('messenger'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'matomo_reports_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, array $sites = NULL) {
    // $config = \Drupal::config('matomo_reports.matomoreportssettings');
    $config = $this->configFactory->get('matomo_reports.matomoreportssettings');
    $session = $this->getRequest()->getSession();
    $allowed_sites = [];
    $allowed_keys = explode(',', $config->get('matomo_reports_allowed_sites'));
    $form['#attributes'] = [
      'class' => [
        'search-form',
        'container-inline',
      ]
    ];
    $form['matomo_filters'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Select site and time period'),
    ];
    $period = [
      0 => $this->t('Today'),
      1 => $this->t('Yesterday'),
      2 => $this->t('Last week'),
      3 => $this->t('Last month'),
      4 => $this->t('Last year'),
    ];
    $form['matomo_filters']['period'] = [
      '#type' => 'select',
      '#title' => $this->t('When'),
      '#description' => $this->t('Report Period'),
      '#options' => $period,
      '#size' => 1,
      '#default_value' => $session->get('matomo_reports_period') ?? 0,
      '#weight' => '0',
    ];
    if ($sites) {
      if (!function_exists('array_key_first')) {
        function array_key_first(array $arr) {
          foreach($arr as $key => $unused) {
            return $key;
          }
          return NULL;
        }
      }
      foreach ($sites as $site) {
        if (empty($allowed_keys[0]) || in_array($site['idsite'], $allowed_keys)) {
          $allowed_sites[$site['idsite']] = $site['name'];
        }
        if ($session->get('matomo_reports_site') == $site['idsite']) {
          $session_site_exists = TRUE;
        }
      }
      if ($session->get('matomo_reports_site') == '' || !$session_site_exists || !array_key_exists($session->get('matomo_reports_site'), $allowed_sites)) {
        // When not set, set to first of the allowed sites.
        $session->set('matomo_reports_site', array_key_first($allowed_sites));
      }
      if (count($allowed_sites) > 1) {
        $form['matomo_filters']['site'] = array(
          '#type' => 'select',
          '#title' => $this->t('Site'),
          '#weight' => -5,
          '#default_value' => $session->get('matomo_reports_site'),
          '#options' => $allowed_sites,
        );
      }
      elseif (count($allowed_sites) == 1) {
        foreach ($allowed_sites as $siteid => $sitename) {
          break;
        }
        $form['matomo_filters']['site'] = array(
          '#type' => 'hidden',
          '#value' => $siteid,
        );
        $form['matomo_filters']['sitename'] = array(
          '#type' => 'textfield',
          '#title' => $this->t('Site'),
          '#weight' => -5,
          '#size' => 25,
          '#value' => $sitename,
          '#disabled' => TRUE,
        );
        $form['matomo_filters']['period']['#attributes'] = ['onchange' => 'this.form.submit();'];
      }
    }
    $form['matomo_filters']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $session = $this->getRequest()->getSession();
    $session->set('matomo_reports_period', $form_state->getValue('period'));
    $session->set('matomo_reports_site', $form_state->getValue('site'));
  }
}
