<?php

namespace Drupal\openstack\Plugin\cloud\server_template;

use Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface;
use Drupal\cloud\Entity\CloudServerTemplateInterface;
use Drupal\cloud\Plugin\cloud\CloudPluginBase;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Plugin\cloud\server_template\CloudServerTemplatePluginInterface;
use Drupal\cloud\Service\EntityLinkRendererInterface;
use Drupal\cloud\Service\Util\EntityLinkWithShortNameHtmlGenerator;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\openstack\Entity\OpenStackInstance;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * OpenStack cloud server template plugin.
 */
class OpenStackServerTemplatePlugin extends CloudPluginBase implements CloudServerTemplatePluginInterface, ContainerFactoryPluginInterface {

  /**
   * The OpenStack EC2 Service.
   *
   * @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface
   */
  protected $ec2Service;

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
   * OpenStackCloudServerTemplatePlugin constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface $ec2_service
   *   The AWS Cloud EC2 Service.
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
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              Ec2ServiceInterface $ec2_service,
                              EntityTypeManagerInterface $entity_type_manager,
                              UuidInterface $uuid_service,
                              AccountProxyInterface $current_user,
                              ConfigFactoryInterface $config_factory,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
                              EntityLinkRendererInterface $entity_link_renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->ec2Service = $ec2_service;
    $this->entityTypeManager = $entity_type_manager;
    $this->uuidService = $uuid_service;
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
    $this->entityLinkRenderer = $entity_link_renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('openstack.ec2'),
      $container->get('entity_type.manager'),
      $container->get('uuid'),
      $container->get('current_user'),
      $container->get('config.factory'),
      $container->get('plugin.manager.cloud_config_plugin'),
      $container->get('entity.link_renderer')
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
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   */
  public function launch(CloudServerTemplateInterface $cloud_server_template, FormStateInterface $form_state = NULL) {
    $this->ec2Service->setCloudContext($cloud_server_template->getCloudContext());

    $params = [];
    $params['ImageId'] = $cloud_server_template->get('field_openstack_image_id')->value;
    $params['MaxCount'] = $cloud_server_template->get('field_max_count')->value;
    $params['MinCount'] = $cloud_server_template->get('field_min_count')->value;
    $params['Monitoring']['Enabled'] = $cloud_server_template->get('field_monitoring')->value === '0' ? FALSE : TRUE;
    $params['InstanceType'] = $cloud_server_template->get('field_instance_type')->value;
    if (isset($cloud_server_template->get('field_openstack_ssh_key')->entity)) {
      $params['KeyName'] = $cloud_server_template->get('field_openstack_ssh_key')->entity->get('key_pair_name')->value;
    }

    $images = $this->entityTypeManager
      ->getStorage('openstack_image')->loadByProperties([
        'image_id' => $params['ImageId'],
      ]
    );
    if (!empty($images) && array_shift($images)->get('root_device_type') === 'ebs') {
      $params['InstanceInitiatedShutdownBehavior'] = $cloud_server_template->get('field_instance_shutdown_behavior')->value;
    }

    // Setup optional parameters.
    if (isset($cloud_server_template->get('field_kernel_id')->value)) {
      $params['KernelId'] = $cloud_server_template->get('field_kernel_id')->value;
    }
    if (isset($cloud_server_template->get('field_ram')->value)) {
      $params['RamdiskId'] = $cloud_server_template->get('field_ram')->value;
    }
    if (isset($cloud_server_template->get('field_user_data')->value)) {
      $params['UserData'] = base64_encode($cloud_server_template->get('field_user_data')->value);
    }

    $params['SecurityGroupIds'] = [];

    if (isset($cloud_server_template->get('field_os_availability_zone')->value)) {
      $params['Placement']['AvailabilityZone'] = $cloud_server_template->get('field_os_availability_zone')->value;
    }

    $vpc_id = NULL;
    if ($cloud_server_template->get('field_openstack_subnet')->value !== NULL) {
      $params['SubnetId'] = $cloud_server_template->get('field_openstack_subnet')->value;
      $vpc_id = $cloud_server_template->get('field_openstack_vpc')->value;
    }

    foreach ($cloud_server_template->get('field_openstack_security_group') ?: [] as $group) {
      if (isset($group->entity)
      && $vpc_id !== NULL
      && $vpc_id === $group->entity->getVpcId()) {
        $params['SecurityGroupIds'][] = $group->entity->getGroupId();
      }
    }

    if (empty($params['SecurityGroupIds'])) {
      unset($params['SecurityGroupIds']);
    }

    if (isset($cloud_server_template->get('field_openstack_network')->entity)) {
      $params['NetworkInterfaces'] = [
        ['NetworkId' => $cloud_server_template->get('field_openstack_network')->entity->getNetworkInterfaceId()],
      ];
    }

    $tags_map = [];

    // Add tags from the template.
    foreach ($cloud_server_template->get('field_tags') ?: [] as $tag_item) {
      $tags_map[$tag_item->getItemKey()] = $tag_item->getItemValue();
    }

    if (!empty($form_state->getValue('termination_protection'))) {
      $params['DisableApiTermination'] = $form_state->getValue('termination_protection') === '0' ? FALSE : TRUE;
    }
    else {
      // If the user checks the auto termination option
      // add it as a tag to Amazon EC2.
      if (!empty($form_state->getValue('terminate'))) {
        /* @var \Drupal\Core\Datetime\DrupalDateTime $timestamp */
        $timestamp = $form_state->getValue('termination_date');
        $tags_map['openstack_' . OpenStackInstance::TAG_TERMINATION_TIMESTAMP] = $timestamp->getTimeStamp();
      }
    }

    $tags_map['Name'] = $cloud_server_template->getName();
    if ($params['MaxCount'] > 1) {
      $cloud_launch_uuid = $this->uuidService->generate();
      $tags_map['Name'] .= $cloud_launch_uuid;
    }

    $tags = [];
    foreach ($tags_map ?: [] as $item_key => $item_value) {
      $tags[] = [
        'Key' => $item_key,
        'Value' => $item_value,
      ];
    }

    if (($result = $this->ec2Service->runInstances($params, $tags)) !== NULL) {
      // Update instances after launch.
      $this->ec2Service->updateInstances();
      if ($params['MaxCount'] > 1) {
        $this->updateInstanceName($cloud_server_template, $cloud_launch_uuid);
      }
      $this->processOperationStatus($cloud_server_template, 'launched');

      $return_route = [
        'route_name' => 'view.openstack_instance.list',
        'params' => ['cloud_context' => $cloud_server_template->getCloudContext()],
      ];
    }
    else {
      $return_route = [
        'route_name' => 'entity.cloud_server_template.canonical',
        'params' => ['cloud_server_template' => $cloud_server_template->id(), 'cloud_context' => $cloud_server_template->getCloudContext()],
      ];
    }

    return $return_route;
  }

  /**
   * {@inheritdoc}
   */
  public function buildListHeader() {
    return [
      [
        'data' => $this->t('AMI Name'),
        'specifier' => 'field_openstack_image_id',
        'field' => 'field_openstack_image_id',
      ],
      [
        'data' => $this->t('Instance Type'),
        'specifier' => 'field_instance_type',
        'field' => 'field_instance_type',
      ],
      [
        'data' => $this->t('Security Group'),
        'specifier' => 'field_openstack_security_group',
        'field' => 'field_openstack_security_group',
      ],
      [
        'data' => $this->t('Key Pair'),
        'specifier' => 'field_openstack_ssh_key',
        'field' => 'field_openstack_ssh_key',
      ],
      [
        'data' => $this->t('VPC'),
        'specifier' => 'field_openstack_vpc',
        'field' => 'field_openstack_vpc',
      ],
      [
        'data' => $this->t('Max Count'),
        'specifier' => 'field_max_count',
        'field' => 'field_max_count',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildListRow(CloudServerTemplateInterface $entity) {
    $row = [];
    $empty_row = ['data' => ['#markup' => '']];

    // AMI image.
    $image = NULL;
    $image_id = $entity->get('field_openstack_image_id')->value;
    if (!empty($image_id)) {
      $images = $this->entityTypeManager
        ->getStorage('openstack_image')->loadByProperties([
          'image_id' => $image_id,
        ]);
      $image = array_shift($images);
    }

    if ($image !== NULL) {
      $element = $this->entityLinkRenderer->renderViewElement(
        $image->getImageId(),
        'openstack_image',
        'image_id',
        [],
        $image->getName()
      );
      $row[]['data'] = ['#markup' => $element['#markup']];
    }
    else {
      $row[] = $empty_row;
    }

    // Instance type.
    $instance_type = $entity->get('field_instance_type')->value;
    $row[] = [
      'data' => [
        '#type' => 'link',
        '#url' => Url::fromRoute(
          'aws_cloud.instance_type_prices',
          ['cloud_context' => $entity->getCloudContext()],
          ['fragment' => $instance_type]
        ),
        '#title' => $instance_type,
      ],
    ];

    // Security groups.
    $htmls = [];
    foreach ($entity->get('field_openstack_security_group') ?: [] as $group) {
      if ($group->entity !== NULL) {
        $group_id = $group->entity->getGroupId();
        $element = $this->entityLinkRenderer->renderViewElement(
          $group_id,
          'openstack_security_group',
          'group_id',
          [],
          $group->entity->getName()
        );

        $htmls[] = $element['#markup'];
      }
      else {
        $htmls[] = '';
      }
    }
    $row[] = [
      'data' => ['#markup' => implode(', ', $htmls)],
    ];

    // SSH key.
    if ($entity->get('field_openstack_ssh_key')->entity !== NULL) {

      $row[] = [
        'data' => $this->entityLinkRenderer->renderViewElement(
          $entity->get('field_openstack_ssh_key')->entity->getKeyPairName(),
          'openstack_key_pair',
          'key_pair_name'
        ),
      ];
    }
    else {
      $row[] = $empty_row;
    }

    // VPC.
    if ($entity->get('field_openstack_vpc')->value !== NULL) {
      $row[] = [
        'data' => $this->entityLinkRenderer->renderViewElement(
          $entity->get('field_openstack_vpc')->value,
          'aws_cloud_vpc',
          'vpc_id',
          [],
          '',
          EntityLinkWithShortNameHtmlGenerator::class
        ),
      ];
    }
    else {
      $row[] = $empty_row;
    }

    $row[]['data']['#markup'] = $entity->get('field_max_count')->value;

    return $row;
  }

  /**
   * Update instance name based on the name of the cloud server template.
   *
   * If the same instance name exists, the number suffix (#2, #3…) can be
   * added at the end of the cloud server template name.
   *
   * @param \Drupal\cloud\Entity\CloudServerTemplateInterface $cloud_server_template
   *   The cloud server template used to launch a instance.
   * @param string $cloud_launch_uuid
   *   The uuid to specify instances.
   */
  private function updateInstanceName(
    CloudServerTemplateInterface $cloud_server_template,
    $cloud_launch_uuid
  ) {
    $template_name = $cloud_server_template->getName();
    $cloud_context = $cloud_server_template->getCloudContext();

    $instance_storage = $this->entityTypeManager->getStorage('openstack_instance');
    $instance_ids = $instance_storage
      ->getQuery()
      ->condition('name', $template_name . $cloud_launch_uuid)
      ->condition('cloud_context', $cloud_context)
      ->execute();

    $instances = $instance_storage->loadMultiple($instance_ids);
    $count = 1;
    $prefix = $this->getInstanceNamePrefix($template_name, $cloud_context);
    foreach ($instances ?: [] as $instance) {
      $name = $prefix . $count++;
      $params = [
        'Resources' => [$instance->getInstanceId()],
      ];
      $params['Tags'][] = [
        'Key' => 'Name',
        'Value' => $name,
      ];
      $this->ec2Service->createTags($params);
    }

    if (count($instances) > 0) {
      $this->ec2Service->updateInstances();
    }
  }

  /**
   * Get the prefix of instance name.
   *
   * The prefix will be something like below.
   * 1. 1st Launch:
   *   Cloud Orchestrator #1, Cloud Orchestrator #2.
   * 2. 2nd Launch:
   *   Cloud Orchestrator #2-1, Cloud Orchestrator #2-2.
   * 2. 3nd Launch:
   *   Cloud Orchestrator #3-1, Cloud Orchestrator #3-2.
   *
   * @param string $template_name
   *   The template name.
   * @param string $cloud_context
   *   The cloud context.
   *
   * @return string
   *   The prefix of instance name.
   */
  private function getInstanceNamePrefix($template_name, $cloud_context) {
    $instance_storage = $this->entityTypeManager->getStorage('openstack_instance');
    $instance_ids = $instance_storage
      ->getQuery()
      ->condition('name', "$template_name #%", 'like')
      ->condition('cloud_context', $cloud_context)
      ->execute();

    $instances = $instance_storage->loadMultiple($instance_ids);

    $instance_names = array_map(static function ($instance) {
      return $instance->getName();
    }, $instances);

    $prefix = "$template_name #";
    if (array_search($prefix . '1', $instance_names) === FALSE) {
      return $prefix;
    }

    $index = 2;
    $prefix = "$template_name #$index-";
    while (array_search($prefix . '1', $instance_names) !== FALSE) {
      $index++;
      $prefix = "$template_name #$index-";
    }

    return $prefix;
  }

}
