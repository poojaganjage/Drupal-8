<?php

namespace Drupal\matomo_reports\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\matomo_reports\MatomoData;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\RendererInterface;

/**
 * Provides a 'MatomoReportsBlock' block.
 *
 * @Block(
 *  id = "matomo_page_report",
 *  admin_label = @Translation("Matomo page statistics"),
 * )
 */
class MatomoReportsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Account Proxy Interface.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The Entity Manager Interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new MatomoReportsBlock object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager interface.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $currentUser, EntityTypeManagerInterface $entityTypeManager, ModuleHandlerInterface $moduleHandler, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $configFactory;
    $this->moduleHandler = $moduleHandler;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
          ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access matomo reports');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    // $renderer = \Drupal::service('renderer');
    $renderer = $this->renderer;
    // $current_user = \Drupal::currentUser();
    $current_user = $this->currentUser;
    $build = [];

    // if (!\Drupal::moduleHandler()->moduleExists('matomo')) {
    if (!$this->moduleHandler->moduleExists('matomo')) {
      $build['#markup'] = $this->t('To use this block, you need to install the <a href=":url">Matomo</a> module', array(':url' => 'https://www.drupal.org/project/matomo'));
      return $build;
    }

    // Build the data URL with all params.
    $token_auth = MatomoData::getToken();
    $matomo_url = MatomoData::getUrl();
    // message when no token?

    if(empty($matomo_url)) {
      $build['#markup'] = $this->t('Please configure the <a href=":url">Matomo settings</a> to use this block.', array(':url' => '/admin/config/system/matomo'));
      return $build;
    }

    $data_params = [];
    // $data_params['idSite'] = \Drupal::config('matomo.settings')->get('site_id');
    $data_params['idSite'] = $this->configFactory->get('matomo.settings')->get('site_id');
    $data_params['date'] = 'today';
    $data_params['period'] = 'year';
    $data_params['module'] = 'API';
    $data_params['method'] = 'Actions.getPageUrl';
    $data_params['pageUrl'] = urldecode($_SERVER['REQUEST_URI']);
    $data_params['format'] = 'JSON';
    if (!empty($token_auth)) {
      $data_params['token_auth'] = $token_auth;
    }
    $query_string = http_build_query($data_params);

    $build['#markup'] = '<div id="matomopageviews"></div>';
    $build['#attached']['library'][] = 'matomo_reports/matomoreports';
    $build['#attached']['drupalSettings']['matomo_reports']['matomoJS']['url'] = $matomo_url;
    $build['#attached']['drupalSettings']['matomo_reports']['matomoJS']['query_string'] = $query_string;
    $build['#cache']['contexts'] = [
      'user',
      'url',
    ];
    // $renderer->addCacheableDependency($build, \Drupal\user\Entity\User::load($current_user->id()));
    $renderer->addCacheableDependency($build, $this->entityTypeManager->getStorage('user')->load($current_user->id()));

    return $build;
  }
}
