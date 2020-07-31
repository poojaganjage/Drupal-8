<?php

namespace Drupal\terraform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Render\RendererInterface;
use Drupal\terraform\Entity\TerraformRunInterface;
use Drupal\terraform\Entity\TerraformWorkspaceInterface;
use Drupal\terraform\Service\TerraformService;
use Drupal\terraform\Service\TerraformServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller responsible for "update" urls.
 *
 * This class is mainly responsible for
 * updating the Terraform entities from urls.
 */
class ApiController extends ControllerBase implements ApiControllerInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The Terraform Service.
   *
   * @var \Drupal\terraform\Service\TerraformServiceInterface
   */
  private $terraformService;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  private $renderer;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * ApiController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\terraform\Service\TerraformServiceInterface $terraform_service
   *   Object for interfacing with Terraform API.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   Messanger Object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack object.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Database\Connection $database
   *   The database service.
   */
  public function __construct(
    EntityTypeManager $entity_type_manager,
    TerraformServiceInterface $terraform_service,
    Messenger $messenger,
    RequestStack $request_stack,
    RendererInterface $renderer,
    Connection $database
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->terraformService = $terraform_service;
    $this->messenger = $messenger;
    $this->requestStack = $request_stack;
    $this->renderer = $renderer;
    $this->database = $database;
  }

  /**
   * Dependency Injection.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Instance of ContainerInterface.
   *
   * @return ApiController
   *   return created object.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('terraform'),
      $container->get('messenger'),
      $container->get('request_stack'),
      $container->get('renderer'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateWorkspaceList($cloud_context) {
    return $this->updateEntityList('terraform_workspace', $cloud_context);
  }

  /**
   * {@inheritdoc}
   */
  public function updateRunList($cloud_context, TerraformWorkspaceInterface $terraform_workspace) {
    return $this->updateEntityList('terraform_run', $cloud_context, $terraform_workspace);
  }

  /**
   * {@inheritdoc}
   */
  public function updateStateList($cloud_context, TerraformWorkspaceInterface $terraform_workspace) {
    return $this->updateEntityList('terraform_state', $cloud_context, $terraform_workspace);
  }

  /**
   * {@inheritdoc}
   */
  public function updateVariableList($cloud_context, TerraformWorkspaceInterface $terraform_workspace) {
    return $this->updateEntityList('terraform_variable', $cloud_context, $terraform_workspace);
  }

  /**
   * {@inheritdoc}
   */
  public function updateRun($cloud_context, TerraformWorkspaceInterface $terraform_workspace, TerraformRunInterface $terraform_run) {
    $this->terraformService->setCloudContext($cloud_context);

    // Update the entity.
    $updated = $this->terraformService->updateRuns([
      'terraform_workspace' => $terraform_workspace,
      'name' => $terraform_run->getName(),
    ], FALSE);

    if ($updated !== FALSE) {
      $this->messageUser(
        $this->t('Updated Terraform Run @name.',
        ['@name' => $terraform_run->getName()])
      );

      // Update the cache.
      TerraformService::clearCacheValue();
    }
    else {
      $this->messageUser(
        $this->t('Unable to update Terraform Run @name.',
        ['@name' => $terraform_run->getName()]),
        'error'
      );
    }

    return $this->redirect('entity.terraform_run.canonical', [
      'cloud_context' => $cloud_context,
      'terraform_workspace' => $terraform_workspace->id(),
      'terraform_run' => $terraform_run->id(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getRunLogs($cloud_context, TerraformWorkspaceInterface $terraform_workspace, TerraformRunInterface $terraform_run) {
    $this->terraformService->setCloudContext($cloud_context);

    // Update the entity.
    $this->terraformService->updateRunLogs([
      'terraform_workspace' => $terraform_workspace,
      'terraform_run' => $terraform_run,
    ]);

    $plan_log_build = $terraform_run->get('plan_log')->view([
      'type' => 'ansi_string_formatter',
      'label' => 'above',
    ]);
    $apply_log_build = $terraform_run->get('apply_log')->view([
      'type' => 'ansi_string_formatter',
      'label' => 'above',
    ]);
    $data = [
      'planLog' => $this->renderer->render($plan_log_build),
      'applyLog' => $this->renderer->render($apply_log_build),
    ];
    return new JsonResponse($data);
  }

  /**
   * Helper method to update entities.
   *
   * @param string $entity_type_name
   *   The entity type name.
   * @param string $cloud_context
   *   The cloud context.
   * @param \Drupal\terraform\Entity\TerraformWorkspaceInterface $terraform_workspace
   *   The teraform workspace entity.
   *
   * @return array
   *   An associative array with a redirect route and any parameters to build
   *   the route.
   */
  private function updateEntityList($entity_type_name, $cloud_context, TerraformWorkspaceInterface $terraform_workspace = NULL) {
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_name);
    $short_name = substr($entity_type->get('id_plural'), strlen('terraform_'));
    $short_name = str_replace('_', '', ucwords($short_name, '_'));

    $this->terraformService->setCloudContext($cloud_context);
    $update_method_name = 'update' . $short_name;
    if (empty($terraform_workspace)) {
      $updated = $this->terraformService->$update_method_name();
    }
    else {
      $updated = $this->terraformService->$update_method_name([
        'terraform_workspace' => $terraform_workspace,
      ]);
    }

    if ($updated !== FALSE) {
      $this->messageUser($this->t('Updated @name.', ['@name' => $short_name]));
      TerraformService::clearCacheValue();
    }
    else {
      $this->messageUser($this->t('Unable to update @name.', ['@name' => $short_name]), 'error');
    }

    $params = ['cloud_context' => $cloud_context];
    if (!empty($terraform_workspace)) {
      $params['terraform_workspace'] = $terraform_workspace->id();
    }

    return $this->redirect("view.$entity_type_name.list", $params);
  }

  /**
   * Helper method to add messages for the end user.
   *
   * @param string $message
   *   The message.
   * @param string $type
   *   The message type: error or message.
   */
  private function messageUser($message, $type = 'message') {
    switch ($type) {
      case 'error':
        $this->messenger->addError($message);
        break;

      case 'message':
        $this->messenger->addStatus($message);
      default:
        break;
    }
  }

}
