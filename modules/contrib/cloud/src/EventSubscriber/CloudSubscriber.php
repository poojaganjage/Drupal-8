<?php

namespace Drupal\cloud\EventSubscriber;

use Drupal\cloud\Service\CloudServiceBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber class for cloud module.
 */
class CloudSubscriber extends CloudServiceBase implements EventSubscriberInterface {

  /**
   * The entity type manager instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new Ec2Service object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   An entity type manager instance.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current account.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              RouteMatchInterface $route_match,
                              AccountInterface $current_user) {

    // The parent constructor takes care of $this->messenger object.
    parent::__construct();

    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
    $this->currentUser = $current_user;
  }

  /**
   * Redirect if there is no cloud service provider (CloudConfig).
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The filter response event.
   */
  public function redirectIfEmpty(FilterResponseEvent $event) {
    $route_names = [
      'view.cloud_config.list',
      'view.cloud_server_template.list',
      'entity.cloud_server_template.collection',
    ];

    try {

      $ids = $this->entityTypeManager
        ->getStorage('cloud_config')
        ->loadMultiple();

      $cloud_config_type = $this->entityTypeManager
        ->getStorage('cloud_config_type')
        ->loadMultiple();

    }
    catch (\Exception $e) {
      // Cloud module is solely enabled w/o any cloud service provider module(s)
      // such as aws_cloud, k8s and/or openstack since there are no cloud_config
      // or cloud_config_type entities.
      $this->entityTypeManager->clearCachedDefinitions();
      return;
    }

    $module_list_url = Url::fromRoute('system.modules_list');

    if (in_array($this->routeMatch->getRouteName(), $route_names)) {
      // Return if not a master request.
      if (!$event->isMasterRequest()) {
        return;
      }

      // Return if not 200.
      $response = $event->getResponse();
      if (!empty($response) && $response->getStatusCode() !== 200) {
        return;
      }

      if (empty($ids)) {
        $url = Url::fromRoute('entity.cloud_config.add_page');
        $page_link = Link::fromTextAndUrl($this->t('cloud service provider'), $url)->toString();
        $this->messenger->addStatus($this->t('There is no cloud service provider. Please create a new @link.', [
          '@link' => $page_link,
        ]));
        $response = new RedirectResponse($url->toString());
        $event->setResponse($response);
      }

      // Redirect to module list page
      // when cloud service provider type is not exist.
      if (empty($ids) && empty($cloud_config_type)) {
        $response = new RedirectResponse($module_list_url->toString());
        $event->setResponse($response);
      }
    }

    if (empty($ids) && empty($cloud_config_type) && $this->currentUser->isAuthenticated() === TRUE) {
      $module_page_link = Link::fromTextAndUrl($this->t('AWS Cloud, Kubernetes, VMware, Openstack and/or Terraform'), $module_list_url)->toString();
      // Using MessengerTrait::messenger().
      $this->messenger->addWarning($this->t('There are no Cloud Service Provider modules enabled. Please enable @link.', [
        '@link' => $module_page_link,
      ]));
    }
  }

  /**
   * Redirect if there is no cloud service provider (CloudConfig).
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The filter response event.
   */
  public function return404IfCloudContextNotExist(FilterResponseEvent $event) {
    $cloud_context = $this->routeMatch->getParameter('cloud_context');
    if (empty($cloud_context)) {
      return;
    }

    $cloud_configs = $this->entityTypeManager
      ->getStorage('cloud_config')
      ->loadByProperties(
        ['cloud_context' => $cloud_context]
      );

    if (empty($cloud_configs)) {
      throw new NotFoundHttpException();
    }
  }

  /**
   * Get Subscribed events.
   *
   * @return string[]
   *   An array of subscribed events.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['redirectIfEmpty'];
    $events[KernelEvents::RESPONSE][] = ['return404IfCloudContextNotExist'];
    return $events;
  }

}
