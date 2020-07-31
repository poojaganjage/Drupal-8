<?php

namespace Drupal\k8s\Plugin\cloud\project;

use Drupal\cloud\Entity\CloudProjectInterface;
use Drupal\cloud\Plugin\cloud\CloudPluginBase;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Plugin\cloud\project\CloudProjectPluginInterface;
use Drupal\cloud\Service\EntityLinkRendererInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\k8s\Service\K8sServiceException;
use Drupal\k8s\Service\K8sServiceInterface;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * K8s Cloud project plugin.
 */
class K8sCloudProjectPlugin extends CloudPluginBase implements CloudProjectPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The K8s Service.
   *
   * @var \Drupal\k8s\Service\K8sServiceInterface
   */
  protected $k8sService;

  /**
   * The Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The UUID service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

  /**
   * Current login user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * Entity link renderer object.
   *
   * @var \Drupal\cloud\Service\EntityLinkRendererInterface
   */
  protected $entityLinkRenderer;

  /**
   * File system object.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * K8sCloudProjectPlugin constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\k8s\Service\K8sServiceInterface $k8s_service
   *   The Kubernetes cluster.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_service
   *   The uuid service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current login user.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   * @param \Drupal\cloud\Service\EntityLinkRendererInterface $entity_link_renderer
   *   The entity link render service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              K8sServiceInterface $k8s_service,
                              EntityTypeManagerInterface $entity_type_manager,
                              UuidInterface $uuid_service,
                              AccountProxyInterface $current_user,
                              ConfigFactoryInterface $config_factory,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
                              EntityLinkRendererInterface $entity_link_renderer,
                              FileSystemInterface $file_system) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->k8sService = $k8s_service;
    $this->entityTypeManager = $entity_type_manager;
    $this->uuidService = $uuid_service;
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
    $this->entityLinkRenderer = $entity_link_renderer;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('k8s'),
      $container->get('entity_type.manager'),
      $container->get('uuid'),
      $container->get('current_user'),
      $container->get('config.factory'),
      $container->get('plugin.manager.cloud_config_plugin'),
      $container->get('entity.link_renderer'),
      $container->get('file_system')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityBundleName() {
    return $this->pluginDefinition['entity_bundle'];
  }

  /**
   * {@inheritdoc}
   */
  public function launch(CloudProjectInterface $cloud_project, FormStateInterface $form_state = NULL) {
    $project_name = $cloud_project->get('name')->value;
    $username = $cloud_project->get('field_username')->value;
    $enable_time_scheduler = $cloud_project->get('field_enable_time_scheduler')->value;
    $enable_resource_scheduler = $cloud_project->get('field_enable_resource_scheduler')->value;
    $k8s_clusters = $cloud_project->get('field_k8s_clusters');
    $route = [
      'route_name' => 'entity.cloud_project.collection',
      'params' => [
        'cloud_context' => $cloud_project->getCloudContext(),
      ],
    ];

    $k8s_resource_list = [
      'k8s_namespace',
      'k8s_resource_quota',
    ];

    $k8s_cluster_all_list = k8s_cluster_allowed_values();
    $k8s_cluster_list = [];
    foreach ($k8s_clusters ?: [] as $cloud_context) {
      if (!empty($cloud_context->value)) {
        $k8s_cluster_list[$cloud_context->value] = $cloud_context->value;
      }
    }

    try {
      // Delete exist resource.
      $diff = array_diff(array_keys($k8s_cluster_all_list), array_keys($k8s_cluster_list));
      k8s_delete_specific_resource($diff, $k8s_resource_list, $project_name);

      // Create K8s namespace.
      $params = [];
      $params['metadata']['name'] = $project_name;
      $params['metadata']['annotations']['entity_type'] = $cloud_project->getEntityType()->getSingularLabel();

      if (!empty($enable_time_scheduler)) {
        $startup_time = Html::escape($cloud_project->get('field_startup_time_hour')->value);
        $startup_time .= ':' . Html::escape($cloud_project->get('field_startup_time_minute')->value);
        $stop_time = Html::escape($cloud_project->get('field_stop_time_hour')->value);
        $stop_time .= ':' . Html::escape($cloud_project->get('field_stop_time_minute')->value);
        $params['metadata']['annotations']['startup_time'] = $startup_time;
        $params['metadata']['annotations']['stop_time'] = $stop_time;
      }

      if (!empty($enable_resource_scheduler)) {

        // Create K8s resource quote.
        $params_resource_quote = [];
        $params_resource_quote['metadata']['name'] = $project_name;

        if ($cloud_project->hasField('field_request_cpu')) {
          $request_cpu = Html::escape($cloud_project->get('field_request_cpu')->value) . 'm';
          $params_resource_quote['spec']['hard']['cpu'] = $request_cpu;
        }
        if ($cloud_project->hasField('field_request_memory')) {
          $request_memory = Html::escape($cloud_project->get('field_request_memory')->value) . 'Mi';
          $params_resource_quote['spec']['hard']['memory'] = $request_memory;
        }
        if ($cloud_project->hasField('field_pod_count')) {
          $pod_count = Html::escape($cloud_project->get('field_pod_count')->value);
          $params_resource_quote['spec']['hard']['pods'] = $pod_count;
        }
      }

      foreach ($k8s_cluster_list ?: [] as $k8s_cluster => $cloud_context) {
        if (empty($cloud_context)) {
          continue;
        }
        $this->k8sService->setCloudContext($cloud_context);
        $this->k8sService->updateNamespaces();
        $this->k8sService->updateResourceQuotas();
        $this->k8sService->updateResourceWithEntity('k8s_namespace', $cloud_context, $project_name, $params);
        if (!empty($enable_resource_scheduler)) {
          $this->k8sService->updateResourceWithEntity('k8s_resource_quota', $cloud_context, $project_name, $params_resource_quote);
        }
        else {
          $resource_quotas = $this->entityTypeManager->getStorage('k8s_resource_quota')
            ->loadByProperties([
              'name' => $project_name,
              'cloud_context' => $cloud_context,
            ]);
          if (!empty($resource_quotas)) {
            $this->k8sService->deleteResourcesWithEntities($resource_quotas);
          }
        }
      }

      $users = $this->entityTypeManager->getStorage('user')
        ->loadByProperties(
          [
            'name' => $username,
          ]);

      $roles = $this->entityTypeManager->getStorage('user_role')
        ->loadByProperties([
          'id' => $project_name,
        ]);

      if (!empty($users)) {
        $user = User::load(array_shift($users)->id());
        $data = [
          'id' => $project_name,
          'label' => $project_name,
        ];

        if (empty($roles)) {
          $role = Role::create($data);
          $role->save();

          // Grant permissions.
          $this->grantPermissions($role, $k8s_cluster_list, $project_name);

          // Add role.
          $user->addRole($role->id());
          $user->save();

          // Add Message.
          $this->processOperationStatus($role, 'created');
        }
      }
      $message_all = $this->messenger->all();
      $messages = array_shift($message_all);
      $this->messenger->deleteAll();

      $output = '';
      foreach ($messages ?: [] as $message) {
        $output .= "<li>${message}</li>";
      }
      $this->messenger->addStatus($this->t('The @type %label has been launched.<ul>@output</ul>', [
        '@type' => $cloud_project->getEntityType()->getSingularLabel(),
        '%label' => $cloud_project->toLink()->toString(),
        '@output' => Markup::Create($output),
      ]));
      $this->logOperationMessage($cloud_project, 'launched');

      return $route;
    }
    catch (K8sServiceException
    | EntityStorageException
    | EntityMalformedException $e) {

      try {

        $this->processOperationErrorStatus($cloud_project, 'launched');

      }
      catch (EntityMalformedException $e) {
        $this->handleException($e);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildListHeader() {
    return [
      [
        'data' => $this->t('Username'),
        'specifier' => 'field_username',
        'field' => 'field_username',
      ],
      [
        'data' => $this->t('k8s cluster'),
        'specifier' => 'field_k8s_clusters',
        'field' => 'field_k8s_clusters',
      ],
      [
        'data' => $this->t('Enable resource scheduler'),
        'specifier' => 'field_enable_resource_scheduler',
        'field' => 'field_enable_resource_scheduler',
      ],
      [
        'data' => $this->t('Enable time scheduler'),
        'specifier' => 'field_enable_time_scheduler',
        'field' => 'field_enable_time_scheduler',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildListRow(CloudProjectInterface $entity) {
    $row['field_username'] = [
      'data' => $this->renderField($entity, 'field_username'),
    ];
    $row['field_k8s_clusters'] = [
      'data' => $this->renderField($entity, 'field_k8s_clusters'),
    ];
    $row['field_enable_resource_scheduler'] = [
      'data' => $this->renderField($entity, 'field_enable_resource_scheduler'),
    ];
    $row['field_enable_time_scheduler'] = [
      'data' => $this->renderField($entity, 'field_enable_time_scheduler'),
    ];
    return $row;
  }

  /**
   * Render a project entity field.
   *
   * @param \Drupal\cloud\Entity\CloudProjectInterface $entity
   *   The project entity.
   * @param string $field_name
   *   The field to render.
   * @param string $view
   *   The view to render.
   *
   * @return array
   *   A fully loaded render array for that field or empty array.
   */
  private function renderField(CloudProjectInterface $entity, $field_name, $view = 'default') {
    $field = [];
    if ($entity->hasField($field_name)) {
      $field = $entity->get($field_name)->view($view);
      // Hide the label.
      $field['#label_display'] = 'hidden';
    }
    return $field;
  }

  /**
   * Grant permissions.
   *
   * @param \Drupal\user\Entity\Role $role
   *   The role entity.
   * @param array $k8s_clusters
   *   The k8s clusters.
   * @param string $namespace
   *   The namespace.
   */
  private function grantPermissions(Role $role, array $k8s_clusters, $namespace) {
    $permissions = [];

    // Dashboard.
    $permissions[] = 'access dashboard';

    // K8s clusters.
    foreach ($k8s_clusters ?: [] as $k8s_cluster) {
      $permissions[] = "view $k8s_cluster";
    }

    // Server Template.
    $permissions[] = 'list cloud server template';
    $permissions[] = 'add cloud server templates';
    $permissions[] = 'delete own cloud server templates';
    $permissions[] = 'edit own cloud server templates';
    $permissions[] = 'view own published cloud server templates';
    $permissions[] = 'view own unpublished cloud server templates';
    $permissions[] = 'access cloud server template revisions';
    $permissions[] = 'revert all cloud server template revisions';
    $permissions[] = 'delete all cloud server template revisions';
    $permissions[] = 'launch cloud server template';

    // Project.
    $permissions[] = 'list cloud project';
    $permissions[] = 'add cloud projects';
    $permissions[] = 'delete own cloud projects';
    $permissions[] = 'edit own cloud projects';
    $permissions[] = 'view own published cloud projects';
    $permissions[] = 'view own unpublished cloud projects';
    $permissions[] = 'access cloud project revisions';
    $permissions[] = 'revert all cloud project revisions';
    $permissions[] = 'delete all cloud project revisions';
    $permissions[] = 'launch cloud project';

    // Access namespace.
    $permissions[] = "view k8s namespace $namespace";

    // Resources CRUD.
    $resources = [
      'namespace',
      'pod',
      'deployment',
      'replica set',
      'cron job',
      'job',
      'service',
      'configmap',
      'secret',
      'stateful set',
      'ingress',
      'daemon set',
      'endpoint',
      'persistent volume',
      'persistent volume claim',
      'cluster role binding',
      'api service',
      'role binding',
      'service account',
      'resource quota',
      'limit range',
      'priority class',
    ];
    foreach ($resources ?: [] as $resource) {
      $permissions[] = "add k8s $resource";
      $permissions[] = "list k8s $resource";

      if ($resource === 'pod' || $resource === 'deployment') {
        $permissions[] = "view own k8s $resource";
        $permissions[] = "edit own k8s $resource";
        $permissions[] = "delete own k8s $resource";
      }
      else {
        $permissions[] = "view k8s $resource";
        // @TODO: Comment out following commands. Otherwise, any user can edit/delete resources.
        $permissions[] = "edit k8s $resource";
        $permissions[] = "delete k8s $resource";
      }
    }

    foreach ($permissions ?: [] as $permission) {
      $role->grantPermission($permission);
    }

    $role->save();
  }

}
