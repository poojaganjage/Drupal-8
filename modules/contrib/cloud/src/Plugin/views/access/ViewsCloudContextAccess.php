<?php

namespace Drupal\cloud\Plugin\views\access;

use Drupal\Core\Url;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\PermissionHandlerInterface;
use Drupal\user\Plugin\views\access\Permission;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Plugin that determines the permission of a view based on the cloud_context.
 *
 * This plugin determines if a particular view can be accessed based on the
 * cloud_context that is in the url and a user configured permission.
 *
 * When a cloud service provider (CloudConfig) entity is added, a new set of
 * permissions is added.
 * That permission determines if a user can access a particular cloud
 * that is part of that cloud context.
 *
 * @ingroup views_access_plugins
 *
 * @ViewsAccess(
 *   id = "ViewsCloudContextAccess",
 *   title = @Translation("Access based on user configured permission and cloud service provider permission"),
 * )
 */
class ViewsCloudContextAccess extends Permission {

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Constructs a new ViewsCloudContextAccess instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\user\PermissionHandlerInterface $permission_handler
   *   The permission handler.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match service.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    PermissionHandlerInterface $permission_handler,
    ModuleHandlerInterface $module_handler,
    RouteMatchInterface $route_match,
    Request $request
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $permission_handler, $module_handler);

    $this->routeMatch = $route_match;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('user.permissions'),
      $container->get('module_handler'),
      $container->get('current_route_match'),
      $container->get('request_stack')->getCurrentRequest());
  }

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    return $this->t('User permission and cloud service provider access check');
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    $cloud_context = $this->routeMatch->getParameter('cloud_context');

    if ($this->routeMatch->getRouteName() === 'views.ajax') {
      // When the request is from ajax, get the cloud context from referer.
      global $base_url;
      // Get the referer url.
      $referer = $this->request->headers->get('referer');
      if (!empty($referer)) {
        // Get the alias or the referer.
        $alias = substr($referer, strlen($base_url));
        $url = Url::fromUri('internal:' . $alias);
        $params = $url->getRouteParameters();
        $cloud_context = !empty($params['cloud_context']) ? $params['cloud_context'] : NULL;
      }
    }
    if (!isset($cloud_context)) {
      return FALSE;
    }
    if (($account->hasPermission('view ' . $cloud_context) || $account->hasPermission('view all cloud service providers'))
      && $account->hasPermission($this->options['perm'])) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRouteDefinition(Route $route) {
    // Use a _custom_access requirement to determine
    // if the the user can access this particular view.
    $route->setRequirement('_custom_access', '\Drupal\cloud\Controller\CloudConfigController::access');
    $route->setOption('perm', $this->options['perm']);
  }

}
