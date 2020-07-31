<?php

namespace Drupal\openstack\Controller;

use Drupal\aws_cloud\Service\Ec2\Ec2Service;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Render\RendererInterface;
use Drupal\views\Views;
use Drupal\openstack\Service\OpenStackEc2Service;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Api controller for interacting with OpenStack.
 */
class ApiController extends ControllerBase implements ApiControllerInterface {

  /**
   * The OpenStack EC2 Service.
   *
   * @var \Drupal\openstack\Service\OpenStackEc2Service
   */
  private $openStackEc2Service;

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
   * ApiController constructor.
   *
   * @param \Drupal\openstack\Service\OpenStackEc2Service $openstack_ec2_service
   *   Object for interfacing with AWS API.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   Messanger Object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack object.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(OpenStackEc2Service $openstack_ec2_service, Messenger $messenger, RequestStack $request_stack, RendererInterface $renderer) {
    $this->openStackEc2Service = $openstack_ec2_service;
    $this->messenger = $messenger;
    $this->requestStack = $request_stack;
    $this->renderer = $renderer;
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
      $container->get('openstack.ec2'),
      $container->get('messenger'),
      $container->get('request_stack'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateInstanceList($cloud_context) {
    $this->openStackEc2Service->setCloudContext($cloud_context);
    $updated = $this->openStackEc2Service->updateInstances();

    if ($updated !== FALSE) {
      $this->messageUser($this->t('Updated Instances.'));
      Ec2Service::clearCacheValue();
    }
    else {
      $this->messageUser($this->t('Unable to update Instances.'), 'error');
    }

    return $this->redirect('entity.openstack_instance.collection', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function updateImageList($cloud_context) {
    $this->openStackEc2Service->setCloudContext($cloud_context);
    $updated = $this->openStackEc2Service->updateImages();

    if ($updated !== FALSE) {
      $this->messageUser($this->t('Updated Images.'));
    }
    else {
      $this->messageUser($this->t('Unable to update Images.'), 'error');
    }

    return $this->redirect('entity.openstack_image.collection', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function updateKeyPairList($cloud_context) {

    $this->openStackEc2Service->setCloudContext($cloud_context);
    $updated = $this->openStackEc2Service->updateKeyPairs();

    if ($updated !== FALSE) {
      $this->messageUser($this->t('Updated Key Pairs.'));
      Ec2Service::clearCacheValue();
    }
    else {
      $this->messageUser($this->t('Unable to update Key Pairs.'), 'error');
    }

    return $this->redirect('view.openstack_key_pair.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function updateSecurityGroupList($cloud_context) {
    $this->openStackEc2Service->setCloudContext($cloud_context);
    $updated = $this->openStackEc2Service->updateSecurityGroups();

    if ($updated !== FALSE) {
      $this->messageUser($this->t('Updated Security Groups.'));
      Ec2Service::clearCacheValue();
    }
    else {
      $this->messageUser($this->t('Unable to update Security Groups.'), 'error');
    }
    return $this->redirect('view.openstack_security_group.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function updateVolumeList($cloud_context) {

    $this->openStackEc2Service->setCloudContext($cloud_context);
    $updated = $this->openStackEc2Service->updateVolumes();

    if ($updated !== FALSE) {
      $this->messageUser($this->t('Updated Volumes.'));
      Ec2Service::clearCacheValue();
    }
    else {
      $this->messageUser($this->t('Unable to update Volumes.'), 'error');
    }

    return $this->redirect('view.openstack_volume.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function updateSnapshotList($cloud_context) {

    $this->openStackEc2Service->setCloudContext($cloud_context);
    $updated = $this->openStackEc2Service->updateSnapshots();

    if ($updated !== FALSE) {
      $this->messageUser($this->t('Updated Snapshots.'));
    }
    else {
      $this->messageUser($this->t('Unable to update Snapshots.'), 'error');
    }

    return $this->redirect('view.openstack_snapshot.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function updateNetworkInterfaceList($cloud_context) {
    $this->openStackEc2Service->setCloudContext($cloud_context);
    $updated = $this->openStackEc2Service->updateNetworkInterfaces();

    if (!empty($updated)) {
      $this->messageUser($this->t('Updated Network Interfaces.'));
      Ec2Service::clearCacheValue();
    }
    else {
      $this->messageUser($this->t('Unable to update Network Interfaces.'), 'error');
    }

    return $this->redirect('view.openstack_network_interface.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function updateFloatingIpList($cloud_context) {

    $this->openStackEc2Service->setCloudContext($cloud_context);
    $updated = $this->openStackEc2Service->updateFloatingIp();

    if ($updated !== FALSE) {
      $this->messageUser($this->t('Updated Floating IPs.'));
      Ec2Service::clearCacheValue();
    }
    else {
      $this->messageUser($this->t('Unable to update Floating IPs.'), 'error');
    }

    return $this->redirect('view.openstack_floating_ip.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function updateAll() {
    $regions = $this->requestStack->getCurrentRequest()->query->get('regions');
    if ($regions === NULL) {
      $this->messageUser($this->t('No region specified'), 'error');
    }
    else {
      $regions_array = explode(',', $regions);

      foreach ($regions_array ?: [] as $region) {
        $entity = $this->entityTypeManager()->getStorage('cloud_config')
          ->loadByProperties(
            [
              'cloud_context' => $region,
            ]);
        if ($entity) {
          openstack_update_resources(array_shift($entity));
        }
      }

      $this->messageUser($this->t('Creating cloud service provider was performed successfully.'));
      drupal_flush_all_caches();
    }
    return $this->redirect('entity.cloud_config.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function listImageCallback($cloud_context) {
    return $this->getViewResponse('openstack_image');
  }

  /**
   * Helper method to get views output.
   *
   * @param string $view_id
   *   The ID of list view.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response of list view.
   *
   * @throws \Exception
   *   Thrown when an object is passed in which cannot be printed.
   */
  private function getViewResponse($view_id) {
    $view = Views::getView($view_id);

    // Set the display machine name.
    $view->setDisplay('list');

    // Render the view as html, and return it as a response object.
    $build = $view->executeDisplay();
    return new Response($this->renderer->render($build));
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
