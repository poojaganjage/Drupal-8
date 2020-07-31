<?php

namespace Drupal\aws_cloud\Controller\Ec2;

use Drupal\aws_cloud\Entity\Ec2\InstanceInterface;
use Drupal\aws_cloud\Service\CloudWatch\CloudWatchServiceInterface;
use Drupal\aws_cloud\Service\Ec2\Ec2Service;
use Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Render\RendererInterface;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller responsible for "update" URLs.
 *
 * This class is mainly responsible for
 * updating the AWS entities from URLs.
 */
class ApiController extends ControllerBase implements ApiControllerInterface {

  /**
   * The AWS Cloud EC2 Service.
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
   * The AWS Cloud CloudWatch Service.
   *
   * @var \Drupal\aws_cloud\Service\CloudWatch\CloudWatchServiceInterface
   */
  private $cloudWatchService;

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
   * @param \Drupal\aws_cloud\Service\CloudWatch\CloudWatchServiceInterface $cloud_watch_service
   *   The AWS Cloud CloudWatch Service.
   */
  public function __construct(
    Ec2ServiceInterface $ec2_service,
    Messenger $messenger,
    RequestStack $request_stack,
    RendererInterface $renderer,
    CloudWatchServiceInterface $cloud_watch_service) {

    $this->ec2Service = $ec2_service;
    $this->messenger = $messenger;
    $this->requestStack = $request_stack;
    $this->renderer = $renderer;
    $this->cloudWatchService = $cloud_watch_service;
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
      $container->get('renderer'),
      $container->get('aws_cloud.cloud_watch')
    );
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
          aws_cloud_update_ec2_resources(array_shift($entity));
        }
      }

      $this->messageUser($this->t('Creating cloud service provider was performed successfully.'));
      drupal_flush_all_caches();
    }
    return $this->redirect('entity.cloud_config.collection');
  }

  /**
   * Update instance message.
   *
   * @param bool $updated
   *   TRUE if entity is updated.
   */
  public function updateInstanceMessage($updated) {
    if ($updated !== FALSE) {
      $this->messageUser($this->t('Updated Instances.'));
      Ec2Service::clearCacheValue();
    }
    else {
      $this->messageUser($this->t('Unable to update Instances.'), 'error');
    }
  }

  /**
   * Update all instances in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to instance list of particular cloud region.
   */
  public function updateInstanceList($cloud_context) {
    $this->ec2Service->setCloudContext($cloud_context);
    $updated = $this->ec2Service->updateInstances();

    $this->updateInstanceMessage($updated);

    return $this->redirect('view.aws_cloud_instance.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * Update all instances of all cloud region.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to all instance list.
   */
  public function updateAllInstanceList() {
    $updated = $this->ec2Service->updateAllInstances();

    $this->updateInstanceMessage($updated);

    return $this->redirect('view.aws_cloud_instance.all');
  }

  /**
   * Get account id.
   *
   * @param array $cloud_config_entities
   *   Cloud config entities array.
   *
   * @return string
   *   The Account id.
   */
  public function getAccountId(array $cloud_config_entities) {
    if (!empty($cloud_config_entities)) {
      $cloud_config = reset($cloud_config_entities);
      $account_id = $cloud_config->get('field_account_id')->value;
      // Use the switch role account_id if switching is enabled.
      $use_assume_role = $cloud_config->get('field_use_assume_role')->value ?? FALSE;
      $use_switch_role = $cloud_config->get('field_use_switch_role')->value ?? FALSE;
      if (!empty($use_assume_role) && !empty($use_switch_role)) {
        $account_id = trim($cloud_config->get('field_switch_role_account_id')->value);
      }
    }

    return $account_id;
  }

  /**
   * Update all images in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to all image list.
   */
  public function updateImageList($cloud_context) {
    $cloud_config_entities = $this->entityTypeManager()->getStorage('cloud_config')->loadByProperties(
      ['cloud_context' => [$cloud_context]]
    );

    if (!empty($cloud_config_entities)) {
      $cloud_config = reset($cloud_config_entities);
    }

    $account_id = $this->getAccountId($cloud_config_entities);

    if ($account_id) {
      $this->ec2Service->setCloudContext($cloud_context);
      $updated = $this->ec2Service->updateImages([
        'Owners' => [
          $account_id,
        ],
      ], TRUE);

      if ($updated !== FALSE) {
        $this->messageUser($this->t('Updated Images.'));
        Ec2Service::clearCacheValue();
      }
      else {
        $this->messageUser($this->t('Unable to update Images.'), 'error');
      }
    }
    else {
      $message = $this->t('AWS User ID is not specified.');
      $account = $this->currentUser();
      if ($account->hasPermission('edit cloud service providers')) {
        $message = Link::createFromRoute($message, 'entity.cloud_config.edit_form', ['cloud_config' => $cloud_config->id()])->toString();
      }
      $this->messageUser($message, 'error');
    }

    return $this->redirect('view.aws_cloud_image.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * Update all images of all cloud region.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to all image list.
   */
  public function updateAllImageList() {
    $cloud_configs = $this->entityTypeManager()
      ->getStorage('cloud_config')
      ->loadByProperties([
        'type' => 'aws_cloud',
      ]);

    foreach ($cloud_configs ?: [] as $cloud_config_entity) {
      $cloud_context = $cloud_config_entity->getCloudContext();

      $cloud_config_entities = $this->entityTypeManager()->getStorage('cloud_config')->loadByProperties(
        ['cloud_context' => [$cloud_context]]
      );

      if (!empty($cloud_config_entities)) {
        $cloud_config = reset($cloud_config_entities);
      }

      $account_id = $this->getAccountId($cloud_config_entities);

      if ($account_id) {

        $this->ec2Service->setCloudContext($cloud_context);
        $updated = $this->ec2Service->updateAllImages([
          'Owners' => [
            $account_id,
          ],
        ], TRUE);

        if ($updated !== FALSE) {
          $this->messageUser($this->t('Updated Images.'));
          Ec2Service::clearCacheValue();
        }
        else {
          $this->messageUser($this->t('Unable to update Images.'), 'error');
        }
      }
      else {
        $message = $this->t('AWS User ID is not specified.');
        $account = $this->currentUser();
        if ($account->hasPermission('edit cloud service providers')) {
          $message = Link::createFromRoute($message, 'entity.cloud_config.edit_form', ['cloud_config' => $cloud_config->id()])->toString();
        }
        $this->messageUser($message, 'error');
      }
    }
    return $this->redirect('view.aws_cloud_image.all');
  }

  /**
   * Update security group message.
   *
   * @param bool $updated
   *   TRUE if entity is updated.
   */
  public function updateSecurityGroupMessage($updated) {
    if ($updated !== FALSE) {
      $this->messageUser($this->t('Updated Security Groups.'));
      Ec2Service::clearCacheValue();
    }
    else {
      $this->messageUser($this->t('Unable to update Security Groups.'), 'error');
    }
  }

  /**
   * Update all security groups in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to all security group list.
   */
  public function updateSecurityGroupList($cloud_context) {
    $this->ec2Service->setCloudContext($cloud_context);
    $updated = $this->ec2Service->updateSecurityGroups();

    $this->updateSecurityGroupMessage($updated);

    return $this->redirect('view.aws_cloud_security_group.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * Update all security groups of all cloud region.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to all security group list.
   */
  public function updateAllSecurityGroupList() {
    $updated = $this->ec2Service->updateAllSecurityGroups();

    $this->updateSecurityGroupMessage($updated);

    return $this->redirect('view.aws_cloud_security_group.all');
  }

  /**
   * Update network interface message.
   *
   * @param bool $updated
   *   TRUE if entity is updated.
   */
  public function updateNetworkInterfaceMessage($updated) {
    if ($updated !== FALSE) {
      $this->messageUser($this->t('Updated Network Interfaces.'));
      Ec2Service::clearCacheValue();
    }
    else {
      $this->messageUser($this->t('Unable to update Network Interfaces.'), 'error');
    }
  }

  /**
   * Update all network interfaces in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to network interface list of particular cloud region.
   */
  public function updateNetworkInterfaceList($cloud_context) {
    $this->ec2Service->setCloudContext($cloud_context);
    $updated = $this->ec2Service->updateNetworkInterfaces();

    $this->updateNetworkInterfaceMessage($updated);

    return $this->redirect('view.aws_cloud_network_interface.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * Update all network interfaces of all cloud region.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to all network interface list.
   */
  public function updateAllNetworkInterfaceList() {
    $updated = $this->ec2Service->updateAllNetworkInterfaces();

    $this->updateNetworkInterfaceMessage($updated);

    return $this->redirect('view.aws_cloud_network_interface.all');
  }

  /**
   * Update all elastic ips in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to elastic ip list of particular cloud region.
   */
  public function updateElasticIpList($cloud_context) {

    $this->ec2Service->setCloudContext($cloud_context);
    $updated = $this->ec2Service->updateElasticIp();

    if ($updated !== FALSE) {
      // Also update Network Interfaces.
      $updated = $this->ec2Service->updateNetworkInterfaces();
      if ($updated !== FALSE) {
        $this->messageUser($this->t('Updated Elastic IPs and Network Interfaces.'));
      }
      else {
        $this->messageUser(
          $this->t('Unable to update Network Interfaces while updating Elastic IPs.'),
          'error');
      }
      Ec2Service::clearCacheValue();
    }
    else {
      $this->messageUser($this->t('Unable to update Elastic IPs.'), 'error');
    }

    return $this->redirect('view.aws_cloud_elastic_ip.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * Update all elastic ips of all cloud region.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to all instance list.
   */
  public function updateAllElasticIpList() {
    $updated = $this->ec2Service->updateAllElasticIp();

    if ($updated !== FALSE) {
      // Also update Network Interfaces.
      $updated = $this->ec2Service->updateAllNetworkInterfaces();
      if ($updated !== FALSE) {
        $this->messageUser($this->t('Updated Elastic IPs and Network Interfaces.'));
      }
      else {
        $this->messageUser(
          $this->t('Unable to update Network Interfaces while updating Elastic IPs.'),
          'error');
      }
      Ec2Service::clearCacheValue();
    }
    else {
      $this->messageUser($this->t('Unable to update Elastic IPs.'), 'error');
    }

    return $this->redirect('view.aws_cloud_elastic_ip.all');
  }

  /**
   * Update keypair message.
   *
   * @param bool $updated
   *   TRUE if entity is updated.
   */
  public function updateKeyPairMessage($updated) {
    if ($updated !== FALSE) {
      $this->messageUser($this->t('Updated Key Pairs.'));
      Ec2Service::clearCacheValue();
    }
    else {
      $this->messageUser($this->t('Unable to update Key Pairs.'), 'error');
    }
  }

  /**
   * Update all keypairs in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to keypair list of particular cloud region.
   */
  public function updateKeyPairList($cloud_context) {

    $this->ec2Service->setCloudContext($cloud_context);
    $updated = $this->ec2Service->updateKeyPairs();

    $this->updateKeyPairMessage($updated);

    return $this->redirect('view.aws_cloud_key_pair.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * Update all keypairs of all cloud region.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to all instance list.
   */
  public function updateAllKeyPairList() {
    $updated = $this->ec2Service->updateAllKeyPairs();

    $this->updateKeyPairMessage($updated);

    return $this->redirect('view.aws_cloud_key_pair.all');
  }

  /**
   * Update volume message.
   *
   * @param bool $updated
   *   TRUE if entity is updated.
   */
  public function updateVolumeMessage($updated) {
    if ($updated !== FALSE) {
      $this->messageUser($this->t('Updated Volumes.'));
      Ec2Service::clearCacheValue();
    }
    else {
      $this->messageUser($this->t('Unable to update Volumes.'), 'error');
    }
  }

  /**
   * Update all volumes in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to volume list of particular cloud region.
   */
  public function updateVolumeList($cloud_context) {
    $this->ec2Service->setCloudContext($cloud_context);
    $updated = $this->ec2Service->updateVolumes();

    $this->updateVolumeMessage($updated);

    return $this->redirect('view.aws_cloud_volume.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * Update all volumes of all cloud region.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to all volume list.
   */
  public function updateAllVolumeList() {
    $updated = $this->ec2Service->updateAllVolumes();

    $this->updateVolumeMessage($updated);

    return $this->redirect('view.aws_cloud_volume.all');
  }

  /**
   * Update snapshot message.
   *
   * @param bool $updated
   *   TRUE if entity is updated.
   */
  public function updateSnapshotMessage($updated) {
    if ($updated !== FALSE) {
      $this->messageUser($this->t('Updated Snapshots.'));
      Ec2Service::clearCacheValue();
    }
    else {
      $this->messageUser($this->t('Unable to update Snapshots.'), 'error');
    }
  }

  /**
   * Update all snapshots in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to snapshot list of particular cloud region.
   */
  public function updateSnapshotList($cloud_context) {

    $this->ec2Service->setCloudContext($cloud_context);
    $updated = $this->ec2Service->updateSnapshots();

    $this->updateSnapshotMessage($updated);

    return $this->redirect('view.aws_cloud_snapshot.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

  /**
   * Update all snapshots of all cloud region.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to all snapshot list.
   */
  public function updateAllSnapshotList() {
    $updated = $this->ec2Service->updateAllSnapshots();

    $this->updateSnapshotMessage($updated);

    return $this->redirect('view.aws_cloud_snapshot.all');
  }

  /**
   * {@inheritdoc}
   */
  public function listInstanceCallback($cloud_context) {
    return $this->getViewResponse('aws_cloud_instance');
  }

  /**
   * {@inheritdoc}
   */
  public function listImageCallback($cloud_context) {
    return $this->getViewResponse('aws_cloud_image');
  }

  /**
   * {@inheritdoc}
   */
  public function listSnapshotCallback($cloud_context) {
    return $this->getViewResponse('aws_cloud_snapshot');
  }

  /**
   * {@inheritdoc}
   */
  public function listVolumeCallback($cloud_context) {
    return $this->getViewResponse('aws_cloud_volume');
  }

  /**
   * {@inheritdoc}
   */
  public function searchImages($cloud_context) {
    $this->ec2Service->setCloudContext($cloud_context);
    $name = $this->requestStack->getCurrentRequest()->query->get('q');
    $result = $this->ec2Service->describeImages([
      'Filters' => [
        [
          'Name' => 'name',
          'Values' => [$name],
        ],
        [
          'Name' => 'state',
          'Values' => ['available'],
        ],
      ],
    ]);

    $limit = 50;
    $count = 0;
    $images = [];
    foreach ($result['Images'] ?: [] as $image) {
      $images[] = [
        'name' => $image['Name'],
        'id' => $image['Name'],
        'created' => strtotime($image['CreationDate']),
      ];
      if ($count++ >= $limit) {
        break;
      }
    }

    // Sort by latest.
    usort($images, static function ($a, $b) {
      return ($a['created'] < $b['created']) ? 1 : -1;
    });

    return new Response(json_encode($images));
  }

  /**
   * {@inheritdoc}
   */
  public function getInstanceMetrics($cloud_context, InstanceInterface $aws_cloud_instance) {
    $this->cloudWatchService->setCloudContext($cloud_context);
    $metric_names = [
      'cpu' => 'CPUUtilization',
      'network_in' => 'NetworkIn',
      'network_out' => 'NetworkOut',
      'disk_read' => 'DiskReadBytes',
      'disk_write' => 'DiskWriteBytes',
      'disk_read_operation' => 'DiskReadOps',
      'disk_write_operation' => 'DiskWriteOps',
    ];
    $queries = [];
    foreach ($metric_names as $key => $name) {
      $queries[] = [
        'Id' => $key,
        'MetricStat' => [
          'Metric' => [
            'Namespace' => 'AWS/EC2',
            'MetricName' => $name,
            'Dimensions' => [
              [
                'Name' => 'InstanceId',
                'Value' => $aws_cloud_instance->getInstanceId(),
              ],
            ],
          ],
          'Period' => 300,
          'Stat' => 'Average',
        ],
      ];
    }

    $result = $this->cloudWatchService->getMetricData([
      'StartTime' => strtotime('-1 days'),
      'EndTime' => strtotime('now'),
      'MetricDataQueries' => $queries,
    ]);

    $data = [];
    foreach (array_keys($metric_names) as $index => $key) {
      $timestamps = [];
      foreach ($result['MetricDataResults'][$index]['Timestamps'] as $timestamp) {
        $timestamps[] = $timestamp->__toString();
      }

      $data[$key] = [
        'timestamps' => $timestamps,
        'values' => $result['MetricDataResults'][$index]['Values'],
      ];

      if ($key === 'network_in' || $key === 'network_out') {
        // Convert Byte to MB.
        $data[$key]['values'] = array_map(function ($value) {
          return $value / 1024 / 1024;
        }, $data[$key]['values']);
      }
    }

    return new JsonResponse($data);
  }

  /**
   * Helper method to get views output.
   *
   * @param string $view_id
   *   The ID of list view.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response of list view.
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
