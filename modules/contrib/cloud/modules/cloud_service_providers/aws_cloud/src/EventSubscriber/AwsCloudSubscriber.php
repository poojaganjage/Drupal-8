<?php

namespace Drupal\aws_cloud\EventSubscriber;

use Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Service\CloudServiceBase;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber class for aws cloud module.
 */
class AwsCloudSubscriber extends CloudServiceBase implements EventSubscriberInterface {

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
   * The ec2 service object.
   *
   * @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface
   */
  private $ec2Service;

  /**
   * The cache bin.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cache;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  private $cloudConfigPluginManager;

  /**
   * Constructs a new Ec2Service object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   An entity type manager instance.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface $ec2_service
   *   The AWS Cloud EC2 Service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache bin.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              RouteMatchInterface $route_match,
                              Ec2ServiceInterface $ec2_service,
                              CacheBackendInterface $cache,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager) {

    // The parent constructor takes care of $this->messenger object.
    parent::__construct();

    $this->entityTypeManager = $entity_type_manager;

    $this->routeMatch = $route_match;

    $this->ec2Service = $ec2_service;
    $this->cache = $cache;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
  }

  /**
   * Display a warning message about EC2-Classic support on edit pages.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The response event.
   */
  public function displayEc2ClassicMessage(FilterResponseEvent $event) {
    $route_names = [
      'entity.cloud_server_template.launch',
      'entity.cloud_server_template.add_form',
      'entity.cloud_server_template.edit_form',
      'entity.aws_cloud_instance.edit_form',
      'entity.aws_cloud_image.add_form',
      'entity.aws_cloud_image.edit_form',
      'entity.aws_cloud_network_interface.add_form',
      'entity.aws_cloud_network_interface.edit_form',
      'entity.aws_cloud_elastic_ip.add_form',
      'entity.aws_cloud_elastic_ip.edit_form',
      'entity.aws_cloud_security_group.add_form',
      'entity.aws_cloud_security_group.edit_form',
      'entity.aws_cloud_key_pair.add_form',
      'entity.aws_cloud_key_pair.edit_form',
      'entity.aws_cloud_volume.add_form',
      'entity.aws_cloud_volume.edit_form',
      'entity.aws_cloud_snapshot.add_form',
      'entity.aws_cloud_snapshot.edit_form',
    ];

    // Only care about HTML responses.
    if (stripos($event->getResponse()->headers->get('Content-Type'), 'text/html') !== FALSE) {
      if (in_array($this->routeMatch->getRouteName(), $route_names)) {
        $cloud_context = $this->routeMatch->getParameter('cloud_context');
        // Only run the following for AWS.
        $this->cloudConfigPluginManager->setCloudContext($cloud_context);
        /* @var \Drupal\cloud\Entity\CloudConfig $config_entity */
        $config_entity = $this->cloudConfigPluginManager->loadConfigEntity();
        if ($config_entity->id() === 'aws_cloud') {
          $platforms = $this->getSupportedPlatforms($cloud_context);
          if (count($platforms) === 2) {
            // EC2 and VPC platforms supported, throw up a message.
            // Using MessengerTrait::messenger().
            $this->messenger->addWarning($this->t('Your AWS account supports EC2-Classic. Please note aws_cloud module does not support EC2-Classic.'));
          }
        }
      }
    }
  }

  /**
   * Redirect to add form if entity is empty.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The response event.
   */
  public function redirectIfEmpty(FilterResponseEvent $event) {
    $route_names = [
      'entity.cloud_server_template.add_form',
    ];
    if (in_array($this->routeMatch->getRouteName(), $route_names)) {
      // Return if not a master request.
      if (!$event->isMasterRequest()) {
        return;
      }

      // Return if not 200.
      $response = $event->getResponse();
      if ($response->getStatusCode() !== 200) {
        return;
      }

      /* @var \Drupal\cloud\Entity\CloudServerTemplateTypeInterface $cloud_server_template_type */
      $cloud_server_template_type = $this->routeMatch->getParameter('cloud_server_template_type');
      if ($cloud_server_template_type === NULL || $cloud_server_template_type->id() !== 'aws_cloud') {
        return;
      }

      $cloud_context = $this->routeMatch->getParameter('cloud_context');
      if ($cloud_context === NULL) {
        return;
      }

      // Check whether key pair entity exists.
      $ids = $this->entityTypeManager
        ->getStorage('aws_cloud_key_pair')
        ->getQuery()
        ->condition('cloud_context', $cloud_context)
        ->execute();

      if (empty($ids)) {
        // Using MessengerTrait::messenger().
        $this->messenger->addStatus($this->t('There is no Key Pair. Please create a new one.'));
        $response = new RedirectResponse(
          Url::fromRoute(
            'entity.aws_cloud_key_pair.add_form',
            ['cloud_context' => $cloud_context]
          )->toString()
        );
        $event->setResponse($response);
        return;
      }

      // Check whether security group entity exists.
      $ids = $this->entityTypeManager
        ->getStorage('aws_cloud_security_group')
        ->getQuery()
        ->condition('cloud_context', $cloud_context)
        ->execute();

      if (empty($ids)) {
        // Using MessengerTrait::messenger().
        $this->messenger->addStatus($this->t('There is no Security Group. Please create a new one.'));
        $response = new RedirectResponse(
          Url::fromRoute(
            'entity.aws_cloud_security_group.add_form',
            ['cloud_context' => $cloud_context]
          )->toString()
        );
        $event->setResponse($response);
      }
    }
  }

  /**
   * Helper function to retrieve the supported platforms.
   *
   * @param string $cloud_context
   *   The cloud context to use for the API call.
   *
   * @return array
   *   Array of platforms.
   */
  private function getSupportedPlatforms($cloud_context) {
    $cache = $this->cache->get('ec2.supported_platforms');
    if ($cache) {
      $platforms = $cache->data;
    }
    else {
      $this->ec2Service->setCloudContext($cloud_context);
      $platforms = $this->ec2Service->getSupportedPlatforms();
      $this->cache->set('ec2.supported_platforms', $platforms);
    }
    return $platforms;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['redirectIfEmpty'];
    $events[KernelEvents::RESPONSE][] = ['displayEc2ClassicMessage'];
    return $events;
  }

}
