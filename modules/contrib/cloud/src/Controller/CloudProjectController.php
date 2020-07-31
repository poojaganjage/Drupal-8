<?php

namespace Drupal\cloud\Controller;

use Drupal\cloud\Entity\CloudProjectInterface;
use Drupal\cloud\Plugin\cloud\project\CloudProjectPluginManagerInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CloudProjectController.
 *
 *  Returns responses for cloud project routes.
 */
class CloudProjectController extends ControllerBase implements ContainerInjectionInterface, CloudProjectControllerInterface {

  /**
   * The CloudProjectPluginManager.
   *
   * @var \Drupal\cloud\Plugin\cloud\project\CloudProjectPluginManagerPluginManager
   */
  protected $serverTemplatePluginManager;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Render\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs an OperationsController object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\cloud\Plugin\cloud\project\CloudProjectPluginManagerInterface $project_plugin_manager
   *   The cloud project plugin manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The render service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler interface.
   */
  public function __construct(
    RouteMatchInterface $route_match,
    CloudProjectPluginManagerInterface $project_plugin_manager,
    RendererInterface $renderer,
    DateFormatterInterface $date_formatter,
    ModuleHandlerInterface $module_handler) {

    $this->routeMatch = $route_match;
    $this->serverTemplatePluginManager = $project_plugin_manager;
    $this->renderer = $renderer;
    $this->dateFormatter = $date_formatter;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('plugin.manager.cloud_project_plugin'),
      $container->get('renderer'),
      $container->get('date.formatter'),
      $container->get('module_handler')
    );
  }

  /**
   * Displays a cloud project  revision.
   *
   * @param int $cloud_project_revision
   *   The cloud project  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($cloud_project_revision) {
    $cloud_project = $this->entityTypeManager()->getStorage('cloud_project')->loadRevision($cloud_project_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('cloud_project');

    try {
      return $view_builder->view($cloud_project);
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }

    return [];
  }

  /**
   * Page title callback for a cloud project  revision.
   *
   * @param int $cloud_project_revision
   *   The cloud project  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($cloud_project_revision) {
    $cloud_project = $this->entityTypeManager()->getStorage('cloud_project')->loadRevision($cloud_project_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => !empty($cloud_project) ? $cloud_project->label() : 'N/A',
      '%date' => !empty($cloud_project) ? $this->dateFormatter->format($cloud_project->getRevisionCreationTime()) : 'N/A',
    ]);
  }

  /**
   * Generates an overview table of older revisions of a cloud project .
   *
   * @param \Drupal\cloud\Entity\CloudProjectInterface $cloud_project
   *   The cloud project entity.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(CloudProjectInterface $cloud_project) {
    $account = $this->currentUser();
    $langcode = $cloud_project->language()->getId();
    $langname = $cloud_project->language()->getName();
    $languages = $cloud_project->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $cloud_project_storage = $this->entityTypeManager()->getStorage('cloud_project');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $cloud_project->label()]) : $this->t('Revisions for %title', ['%title' => $cloud_project->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission('revert all cloud project revisions') || $account->hasPermission('administer cloud projects')));
    $delete_permission = (($account->hasPermission('delete all cloud project revisions') || $account->hasPermission('administer cloud projects')));

    $rows = [];

    $vids = $cloud_project_storage->revisionIds($cloud_project);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\cloud\Entity\CloudProjectInterface $revision */
      $revision = $cloud_project_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid !== $cloud_project->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.cloud_project.revision', [
            'cloud_context' => $cloud_project->getCloudContext(),
            'cloud_project' => $cloud_project->id(),
            'cloud_project_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $cloud_project->toLink($date)->toString();
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations
              ? Url::fromRoute('entity.cloud_project.translation_revert', [
                'cloud_context' => $cloud_project->getCloudContext(),
                'cloud_project' => $cloud_project->id(),
                'cloud_project_revision' => $vid,
                'langcode' => $langcode,
              ])
              : Url::fromRoute('entity.cloud_project.revision_revert', [
                'cloud_context' => $cloud_project->getCloudContext(),
                'cloud_project' => $cloud_project->id(),
                'cloud_project_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.cloud_project.revision_delete', [
                'cloud_context' => $cloud_project->getCloudContext(),
                'cloud_project' => $cloud_project->id(),
                'cloud_project_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['cloud_project_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#sticky' => TRUE,
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function launch(CloudProjectInterface $cloud_project) {
    $redirect_route = $this->projectPluginManager->launch($cloud_project);
    // Let modules alter the redirect after a cloud project has been
    // launched.
    $this->moduleHandler->invokeAll('cloud_project_post_launch_redirect_alter', [&$redirect_route, $cloud_project]);
    return $this->redirect($redirect_route['route_name'], $redirect_route['params'] ?? []);
  }

  /**
   * Checks user access for a specific request based on the cloud context.
   *
   * Supports AND and OR access checks, similar to _permission definition in
   * *.routing.yml.
   *
   * @param string $cloud_context
   *   Cloud context to check.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   * @param \Symfony\Component\Routing\Route $route
   *   The route object.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function access($cloud_context, AccountInterface $account, Route $route) {
    $perm = $route->getOption('perm');
    if (strpos($perm, '+') === FALSE && strpos($perm, ',') === FALSE) {
      if ($account->hasPermission('view all cloud service providers')) {
        return AccessResult::allowedIfHasPermissions($account, [
          $perm,
        ]);
      }
      else {
        return AccessResult::allowedIfHasPermissions($account, [
          $perm,
          'view ' . $cloud_context,
        ]);
      }
    }
    else {
      if (!$account->hasPermission('view all cloud service providers')
        && !$account->hasPermission('view ' . $cloud_context)) {

        return AccessResult::neutral();
      }

      // Allow to conjunct the permissions with OR ('+') or AND (',').
      $split = explode(',', $perm);
      // Support AND permission check.
      if (count($split) > 1) {
        return AccessResult::allowedIfHasPermissions($account, $split, 'AND');
      }
      else {
        $split = explode('+', $perm);
        return AccessResult::allowedIfHasPermissions($account, $split, 'OR');
      }
    }
  }

}
