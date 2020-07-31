<?php

namespace Drupal\vmware\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Render\RendererInterface;
use Drupal\vmware\Service\VmwareServiceInterface;
use Drupal\vmware\Service\VmwareService;
use Drupal\vmware\Service\VmwareServiceException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller responsible for "update" urls.
 *
 * This class is mainly responsible for
 * updating the vmware entities from urls.
 */
class ApiController extends ControllerBase implements ApiControllerInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The VMware Service.
   *
   * @var \Drupal\vmware\Service\VmwareServiceInterface
   */
  private $vmwareService;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\vmware\Service\VmwareServiceInterface $vmware_service
   *   Object for interfacing with VMware API.
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
    EntityTypeManagerInterface $entity_type_manager,
    VmwareServiceInterface $vmware_service,
    Messenger $messenger,
    RequestStack $request_stack,
    RendererInterface $renderer,
    Connection $database
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->vmwareService = $vmware_service;
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
      $container->get('vmware'),
      $container->get('messenger'),
      $container->get('request_stack'),
      $container->get('renderer'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateVmList($cloud_context) {
    return $this->updateEntityList('vm', 'vms', $cloud_context);
  }

  /**
   * Helper method to update entities.
   *
   * @param string $entity_type_name
   *   The entity type name.
   * @param string $entity_type_name_plural
   *   The plural format of entity type name.
   * @param string $cloud_context
   *   The cloud context.
   *
   * @return array
   *   An associative array with a redirect route and any parameters to build
   *   the route.
   */
  private function updateEntityList($entity_type_name, $entity_type_name_plural, $cloud_context) {
    $entity_type_name_capital = str_replace('_', '', ucwords($entity_type_name, '_'));
    $entity_type_name_capital_plural = str_replace('_', '', ucwords($entity_type_name_plural, '_'));

    $this->vmwareService->setCloudContext($cloud_context);
    try {
      $this->vmwareService->login();
      $update_method_name = 'update' . $entity_type_name_capital_plural;
      $updated = $this->vmwareService->$update_method_name();
    }
    catch (VmwareServiceException $e) {
      $updated = FALSE;
    }

    $entity_type = $this->entityTypeManager->getDefinition('vmware_' . $entity_type_name);

    if ($updated !== FALSE) {
      $this->messageUser($this->t('Updated @name.', ['@name' => $entity_type->getPluralLabel()]));
      VmwareService::clearCacheValue();
    }
    else {
      $this->messageUser($this->t('Unable to update @name.', ['@name' => $entity_type->getPluralLabel()]), 'error');
    }

    // Update the cache.
    VmwareService::clearCacheValue();

    return $this->redirect("view.vmware_$entity_type_name.list", [
      'cloud_context' => $cloud_context,
    ]);
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
