<?php

namespace Drupal\aws_cloud\Controller\Vpc;

use Drupal\aws_cloud\Service\Ec2\Ec2Service;
use Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller responsible for "update" URLs.
 *
 * This class is mainly responsible for
 * updating the aws entities from URLs.
 */
class ApiController extends ControllerBase implements ApiControllerInterface {

  /**
   * The Ec2Service.
   *
   * @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface
   */
  private $ec2Service;

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
   * @param \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface $ec2_service
   *   Object for interfacing with AWS API.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   Messenger Object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack object.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(
    Ec2ServiceInterface $ec2_service,
    Messenger $messenger,
    RequestStack $request_stack,
    RendererInterface $renderer
  ) {
    $this->ec2Service = $ec2_service;
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
      $container->get('aws_cloud.ec2'),
      $container->get('messenger'),
      $container->get('request_stack'),
      $container->get('renderer')
    );
  }

  /**
   * Update vpc message.
   *
   * @param bool $updated
   *   TRUE if entity is updated.
   */
  public function updateVpcMessage($updated) {
    if ($updated !== FALSE) {
      $this->messenger->addStatus($this->t('Updated VPCs.'));
      Ec2Service::clearCacheValue();
    }
    else {
      $this->messenger->addError($this->t('Unable to update VPCs.'));
    }
  }

  /**
   * Update all vpcs in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to vpc list of particular cloud region.
   */
  public function updateVpcList($cloud_context) {

    $this->ec2Service->setCloudContext($cloud_context);
    $updated = $this->ec2Service->updateVpcs();

    $this->updateVpcMessage($updated);

    return $this->redirect('view.aws_cloud_vpc.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * Update all vpcs of all cloud region.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to all vpc list.
   */
  public function updateAllVpcList() {
    $updated = $this->ec2Service->updateAllVpcs();

    $this->updateVpcMessage($updated);

    return $this->redirect('view.aws_cloud_vpc.all');
  }

  /**
   * Update vpcpeeringconnection message.
   *
   * @param bool $updated
   *   TRUE if entity is updated.
   */
  public function updateVpcPeeringConnectionMessage($updated) {
    if ($updated !== FALSE) {
      $this->messenger->addStatus($this->t('Updated VPC Peering Connections.'));
      Ec2Service::clearCacheValue();
    }
    else {
      $this->messenger->addError($this->t('Unable to update VPC Peering Connections.'));
    }
  }

  /**
   * Update all vpcpeeringconnections in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to vpcpeeringconnection list of particular cloud region.
   */
  public function updateVpcPeeringConnectionList($cloud_context) {

    $this->ec2Service->setCloudContext($cloud_context);
    $updated = $this->ec2Service->updateVpcPeeringConnections();

    $this->updateVpcPeeringConnectionMessage($updated);

    return $this->redirect('view.aws_cloud_vpc_peering_connection.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * Update all vpcpeeringconnections of all cloud region.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to all vpcpeeringconnection list.
   */
  public function updateAllVpcPeeringConnectionList() {
    $updated = $this->ec2Service->updateAllVpcPeeringConnections();

    $this->updateVpcPeeringConnectionMessage($updated);

    return $this->redirect('view.aws_cloud_vpc_peering_connection.all');
  }

  /**
   * Update subnet message.
   *
   * @param bool $updated
   *   TRUE if entity is updated.
   */
  public function updateSubnetMessage($updated) {
    if ($updated !== FALSE) {
      $this->messenger->addStatus($this->t('Updated Subnets.'));
      Ec2Service::clearCacheValue();
    }
    else {
      $this->messenger->addError($this->t('Unable to update Subnets.'));
    }
  }

  /**
   * Update all subnets in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to subnet list of particular cloud region.
   */
  public function updateSubnetList($cloud_context) {

    $this->ec2Service->setCloudContext($cloud_context);
    $updated = $this->ec2Service->updateSubnets();

    $this->updateSubnetMessage($updated);

    return $this->redirect('view.aws_cloud_subnet.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * Update all subnets of all cloud region.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to all subnet list.
   */
  public function updateAllSubnetList() {
    $updated = $this->ec2Service->updateAllSubnets();

    $this->updateSubnetMessage($updated);

    return $this->redirect('view.aws_cloud_subnet.all');
  }

}
