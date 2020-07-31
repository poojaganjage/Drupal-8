<?php

namespace Drupal\cloud\Controller;

use Drupal\cloud\Entity\CloudServerTemplateInterface;
use Drupal\cloud\Plugin\cloud\server_template\CloudServerTemplatePluginManagerInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CloudServerTemplateController.
 *
 *  Returns responses for cloud server template routes.
 */
class CloudServerTemplateController extends ControllerBase implements ContainerInjectionInterface, CloudServerTemplateControllerInterface {

  /**
   * The CloudServerTemplatePluginManager.
   *
   * @var \Drupal\cloud\Plugin\cloud\server_template\CloudServerTemplatePluginManagerPluginManager
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
   * @param \Drupal\cloud\Plugin\cloud\server_template\CloudServerTemplatePluginManagerInterface $server_template_plugin_manager
   *   The cloud server template plugin manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The render service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler interface.
   */
  public function __construct(
    RouteMatchInterface $route_match,
    CloudServerTemplatePluginManagerInterface $server_template_plugin_manager,
    RendererInterface $renderer,
    DateFormatterInterface $date_formatter,
    ModuleHandlerInterface $module_handler) {

    $this->routeMatch = $route_match;
    $this->serverTemplatePluginManager = $server_template_plugin_manager;
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
      $container->get('plugin.manager.cloud_server_template_plugin'),
      $container->get('renderer'),
      $container->get('date.formatter'),
      $container->get('module_handler')
    );
  }

  /**
   * Displays a cloud server template  revision.
   *
   * @param int $cloud_server_template_revision
   *   The cloud server template  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($cloud_server_template_revision) {
    $cloud_server_template = $this->entityTypeManager()->getStorage('cloud_server_template')->loadRevision($cloud_server_template_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('cloud_server_template');

    try {
      return $view_builder->view($cloud_server_template);
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }

    return [];
  }

  /**
   * Page title callback for a cloud server template  revision.
   *
   * @param int $cloud_server_template_revision
   *   The cloud server template  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($cloud_server_template_revision) {
    $cloud_server_template = $this->entityTypeManager()->getStorage('cloud_server_template')->loadRevision($cloud_server_template_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => !empty($cloud_server_template) ? $cloud_server_template->label() : 'N/A',
      '%date' => !empty($cloud_server_template) ? $this->dateFormatter->format($cloud_server_template->getRevisionCreationTime()) : 'N/A',
    ]);
  }

  /**
   * Generates an overview table of older revisions of a cloud server template .
   *
   * @param \Drupal\cloud\Entity\CloudServerTemplateInterface $cloud_server_template
   *   The cloud server template entity.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(CloudServerTemplateInterface $cloud_server_template) {
    $account = $this->currentUser();
    $langcode = $cloud_server_template->language()->getId();
    $langname = $cloud_server_template->language()->getName();
    $languages = $cloud_server_template->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $cloud_server_template_storage = $this->entityTypeManager()->getStorage('cloud_server_template');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $cloud_server_template->label()]) : $this->t('Revisions for %title', ['%title' => $cloud_server_template->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission('revert all cloud server template revisions') || $account->hasPermission('administer cloud server templates')));
    $delete_permission = (($account->hasPermission('delete all cloud server template revisions') || $account->hasPermission('administer cloud server templates')));

    $rows = [];

    $vids = $cloud_server_template_storage->revisionIds($cloud_server_template);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) ?: [] as $vid) {
      /** @var \Drupal\cloud\Entity\CloudServerTemplateInterface $revision */
      $revision = $cloud_server_template_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid !== $cloud_server_template->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.cloud_server_template.revision', [
            'cloud_context' => $cloud_server_template->getCloudContext(),
            'cloud_server_template' => $cloud_server_template->id(),
            'cloud_server_template_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $cloud_server_template->toLink($date)->toString();
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
          foreach ($row ?: [] as &$current) {
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
              ? Url::fromRoute('entity.cloud_server_template.translation_revert', [
                'cloud_context' => $cloud_server_template->getCloudContext(),
                'cloud_server_template' => $cloud_server_template->id(),
                'cloud_server_template_revision' => $vid,
                'langcode' => $langcode,
              ])
              : Url::fromRoute('entity.cloud_server_template.revision_revert', [
                'cloud_context' => $cloud_server_template->getCloudContext(),
                'cloud_server_template' => $cloud_server_template->id(),
                'cloud_server_template_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.cloud_server_template.revision_delete', [
                'cloud_context' => $cloud_server_template->getCloudContext(),
                'cloud_server_template' => $cloud_server_template->id(),
                'cloud_server_template_revision' => $vid,
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

    $build['cloud_server_template_revisions_table'] = [
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
  public function launch(CloudServerTemplateInterface $cloud_server_template) {
    $redirect_route = $this->serverTemplatePluginManager->launch($cloud_server_template);
    // Let modules alter the redirect after a cloud server template has been
    // launched.
    $this->moduleHandler->invokeAll('cloud_server_template_post_launch_redirect_alter', [&$redirect_route, $cloud_server_template]);
    return $this->redirect($redirect_route['route_name'], $redirect_route['params'] ?? []);
  }

}
