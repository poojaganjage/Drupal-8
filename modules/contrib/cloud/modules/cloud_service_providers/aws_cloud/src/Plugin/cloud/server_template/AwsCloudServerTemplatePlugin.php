<?php

namespace Drupal\aws_cloud\Plugin\cloud\server_template;

use Drupal\aws_cloud\Entity\Ec2\Instance;
use Drupal\aws_cloud\Entity\Ec2\SecurityGroup;
use Drupal\aws_cloud\Entity\Vpc\Subnet;
use Drupal\aws_cloud\Entity\Vpc\Vpc;
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
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AWS Cloud cloud server template plugin.
 */
class AwsCloudServerTemplatePlugin extends CloudPluginBase implements CloudServerTemplatePluginInterface, ContainerFactoryPluginInterface {

  /**
   * The AWS Cloud EC2 Service.
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
   * AwsCloudServerTemplatePlugin constructor.
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
      $container->get('aws_cloud.ec2'),
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

    $subnet_id = NULL;
    $security_group_id = NULL;
    if (!empty($form_state) && !empty($form_state->getValue('automatically_assign_vpc'))) {
      [$subnet_id, $security_group_id] = $this->automaticallyAssignVpc($form_state, $cloud_server_template->getCloudContext());

      if (empty($subnet_id) || empty($security_group_id)) {
        $this->messenger->addError($this->t('Failed to launch an instance because it failed to assign a VPC automatically.'));
        $return_route = [
          'route_name' => 'view.aws_cloud_instance.list',
          'params' => ['cloud_context' => $cloud_server_template->getCloudContext()],
        ];
        return $return_route;
      }
    }

    $params = [];
    $params['DryRun'] = $cloud_server_template->get('field_test_only')->value === '0' ? FALSE : TRUE;
    $params['ImageId'] = $cloud_server_template->get('field_image_id')->value;
    $params['MaxCount'] = $cloud_server_template->get('field_max_count')->value;
    $params['MinCount'] = $cloud_server_template->get('field_min_count')->value;
    $params['Monitoring']['Enabled'] = $cloud_server_template->get('field_monitoring')->value === '0' ? FALSE : TRUE;
    $params['InstanceType'] = $cloud_server_template->get('field_instance_type')->value;
    if (isset($cloud_server_template->get('field_ssh_key')->entity)) {
      $params['KeyName'] = $cloud_server_template->get('field_ssh_key')->entity->get('key_pair_name')->value;
    }

    $images = $this->entityTypeManager
      ->getStorage('aws_cloud_image')
      ->loadByProperties([
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
    if (!empty($form_state->getValue('automatically_assign_vpc'))) {
      // The availability zone will be ignored,
      // if the VPC was automatically assigned.
      $params['SubnetId'] = $subnet_id;
      $params['SecurityGroupIds'][] = $security_group_id;
    }
    else {
      if (isset($cloud_server_template->get('field_availability_zone')->value)) {
        $params['Placement']['AvailabilityZone'] = $cloud_server_template->get('field_availability_zone')->value;
      }

      $vpc_id = NULL;
      if ($cloud_server_template->get('field_subnet')->value !== NULL) {
        $params['SubnetId'] = $cloud_server_template->get('field_subnet')->value;
        $vpc_id = $cloud_server_template->get('field_vpc')->value;
      }

      foreach ($cloud_server_template->get('field_security_group') ?: [] as $group) {
        if (isset($group->entity)
        && $vpc_id !== NULL
        && $vpc_id === $group->entity->getVpcId()) {
          $params['SecurityGroupIds'][] = $group->entity->getGroupId();
        }
      }
    }

    if (empty($params['SecurityGroupIds'])) {
      unset($params['SecurityGroupIds']);
    }

    if (isset($cloud_server_template->get('field_network')->entity)) {
      $params['NetworkInterfaces'] = [
        ['NetworkId' => $cloud_server_template->get('field_network')->entity->getNetworkInterfaceId()],
      ];
    }

    $iam_role = $cloud_server_template->get('field_iam_role')->value;
    if ($iam_role !== NULL) {
      $params['IamInstanceProfile'] = ['Arn' => $iam_role];
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
        $tags_map['aws_cloud_' . Instance::TAG_TERMINATION_TIMESTAMP] = $timestamp->getTimeStamp();
      }
    }

    if (!empty($form_state->getValue('schedule'))) {
      // Send the schedule if scheduler is enabled.
      $config = $this->configFactory->get('aws_cloud.settings');
      $tags_map[$config->get('aws_cloud_scheduler_tag')]
        = $form_state->getValue('schedule');
    }

    $tags_map['Name'] = $cloud_server_template->getName();
    if ($params['MaxCount'] > 1) {
      $cloud_launch_uuid = $this->uuidService->generate();
      $tags_map['Name'] .= $cloud_launch_uuid;
    }

    if (!empty($form_state->getValue('as_bastion'))) {
      $tags_map['aws_cloud_' . Instance::TAG_BASTION] = 1;
      $tags_map['Name'] .= ' bastion';
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

      // If use a bastion, make a connection from bastion VPC to instance VPC.
      $bastion_instance_id = $form_state->getValue('bastion_instance');
      if (!empty($bastion_instance_id)) {
        $bastion_instance = $this->entityTypeManager
          ->getStorage('aws_cloud_instance')->load($bastion_instance_id);

        $bastion_instance_vpc_id = $bastion_instance->getVpcId();
        $instance_vpc_id = $result['Instances'][0]['VpcId'];
        if ($bastion_instance_vpc_id !== $instance_vpc_id) {
          $this->connectVpcs($bastion_instance_vpc_id, $instance_vpc_id);
        }
      }

      $return_route = [
        'route_name' => 'view.aws_cloud_instance.list',
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
        'specifier' => 'field_image_id',
        'field' => 'field_image_id',
      ],
      [
        'data' => $this->t('Instance Type'),
        'specifier' => 'field_instance_type',
        'field' => 'field_instance_type',
      ],
      [
        'data' => $this->t('Security Group'),
        'specifier' => 'field_security_group',
        'field' => 'field_security_group',
      ],
      [
        'data' => $this->t('Key Pair'),
        'specifier' => 'field_ssh_key',
        'field' => 'field_ssh_key',
      ],
      [
        'data' => $this->t('VPC'),
        'specifier' => 'field_vpc',
        'field' => 'field_vpc',
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
    $image_id = $entity->get('field_image_id')->value;
    if (!empty($image_id)) {
      $images = \Drupal::entityTypeManager()
        ->getStorage('aws_cloud_image')
        ->loadByProperties([
          'image_id' => $image_id,
        ]);
      $image = array_shift($images);
    }

    if ($image !== NULL) {
      $element = $this->entityLinkRenderer->renderViewElement(
        $image->getImageId(),
        'aws_cloud_image',
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
    foreach ($entity->get('field_security_group') ?: [] as $group) {
      if ($group->entity !== NULL) {
        $group_id = $group->entity->getGroupId();
        $element = $this->entityLinkRenderer->renderViewElement(
          $group_id,
          'aws_cloud_security_group',
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
    if ($entity->get('field_ssh_key')->entity !== NULL) {

      $row[] = [
        'data' => $this->entityLinkRenderer->renderViewElement(
          $entity->get('field_ssh_key')->entity->getKeyPairName(),
          'aws_cloud_key_pair',
          'key_pair_name'
        ),
      ];
    }
    else {
      $row[] = $empty_row;
    }

    // VPC.
    if ($entity->get('field_vpc')->value !== NULL) {
      $row[] = [
        'data' => $this->entityLinkRenderer->renderViewElement(
          $entity->get('field_vpc')->value,
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

    $instance_storage = $this->entityTypeManager->getStorage('aws_cloud_instance');
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
    $instance_storage = $this->entityTypeManager->getStorage('aws_cloud_instance');
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

  /**
   * Automatically assign a VPC.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   * @param string $cloud_context
   *   The cloud context.
   *
   * @return array
   *   The array including subnet ID and security group ID.
   */
  private function automaticallyAssignVpc(FormStateInterface $form_state, $cloud_context) {
    $current_uid = $this->currentUser->id();
    $this->cloudConfigPluginManager->setCloudContext($cloud_context);
    $cloud_config = $this->cloudConfigPluginManager->loadConfigEntity();

    // Find the VPC owned by current user.
    $this->ec2Service->updateVpcs();
    $vpc_id = NULL;
    $vpcs = $this->entityTypeManager
      ->getStorage('aws_cloud_vpc')
      ->loadByProperties([
        'cloud_context' => $cloud_context,
        'uid' => $current_uid,
      ]);

    if (!empty($vpcs)) {
      $vpc = array_shift($vpcs);
      $vpc_id = $vpc->getVpcId();
      $cidr_block = $vpc->getCidrBlock();
    }

    if ($vpc_id === NULL) {
      // Create an user-owned vpc.
      $cidr_block = $cloud_config->get('field_default_vpc_cidr_block')->value;

      $name = $cloud_config->get('field_default_vpc_name')->value;
      $name = str_replace('[user_name]', $this->currentUser->getAccountName(), $name);

      $vpc_id = $this->createVpc($cidr_block, $name);
    }

    if ($vpc_id === NULL) {
      return [NULL, NULL];
    }

    aws_cloud_create_flow_log($cloud_context, $vpc_id);

    // Find the subnet owned by current user.
    $params['Filters'] = [
      [
        'Name' => 'vpc-id',
        'Values' => [$vpc_id],
      ],
    ];
    $result = $this->ec2Service->describeSubnets($params);
    $subnet_id = NULL;
    foreach ($result['Subnets'] ?: [] as $subnet) {
      foreach ($subnet['Tags'] ?: [] as $tag) {
        if ($tag['Key'] === Subnet::TAG_CREATED_BY_UID && $tag['Value'] === $current_uid) {
          $subnet_id = $subnet['SubnetId'];
          break;
        }
      }

      if (!empty($subnet_id)) {
        break;
      }
    }

    if ($subnet_id === NULL) {
      // Create a user-owned subnet.
      $subnet_cidr_block = $cloud_config->get('field_default_subnet_cidr_block')->value;

      $name = $cloud_config->get('field_default_subnet_name')->value;
      $name = str_replace('[user_name]', $this->currentUser->getAccountName(), $name);

      $subnet_id = $this->createSubnet($vpc_id, $subnet_cidr_block, $name);

      if ($subnet_id === NULL) {
        return [NULL, NULL];
      }
    }

    // Find the security group owned by current user.
    $security_group_id = NULL;
    $security_groups = $this->entityTypeManager
      ->getStorage('aws_cloud_security_group')
      ->loadByProperties([
        'cloud_context' => $cloud_context,
        'vpc_id' => $vpc_id,
        'uid' => $current_uid,
      ]);

    if (!empty($security_groups)) {
      $security_group_id = array_shift($security_groups)->getGroupId();
    }

    if ($security_group_id === NULL) {
      $this->messenger->addError($this->t('No security group was found.'));
      return [NULL, NULL];
    }

    if (!$this->connectUserVpc($cloud_context, $vpc_id)) {
      return [NULL, NULL];
    }

    return [$subnet_id, $security_group_id];
  }

  /**
   * Create a VPC.
   *
   * @param string $cidr_block
   *   The CIDR block.
   * @param string $name
   *   The VPC name.
   *
   * @return string
   *   The ID of the VPC created.
   */
  private function createVpc($cidr_block, $name) {
    $result = $this->ec2Service->createVpc([
      'CidrBlock' => $cidr_block,
    ]);
    if (empty($result['Vpc'])) {
      $this->messenger->addError($this->t(
        'Failed to create a VPC with a CIDR block @cidr_block.',
        ['@cidr_block' => $cidr_block]
      ));
      return NULL;
    }

    $vpc_id = $result['Vpc']['VpcId'];

    $this->ec2Service->createTags([
      'Resources' => [$vpc_id],
      'Tags' => [
        [
          'Key' => Vpc::TAG_CREATED_BY_UID,
          'Value' => $this->currentUser->id(),
        ],
        [
          'Key' => 'Name',
          'Value' => $name,
        ],
      ],
    ]);

    $this->messenger->addStatus($this->t('The VPC @vpc_id was created for the current login user.', [
      '@vpc_id' => $vpc_id,
    ]));

    // Create an entity for the new vpc.
    $this->ec2Service->updateVpcs(['VpcIds' => [$vpc_id]], FALSE);

    // Default security group for the new vpc.
    $params['Filters'] = [
      [
        'Name' => 'vpc-id',
        'Values' => [$vpc_id],
      ],
    ];
    $result = $this->ec2Service->describeSecurityGroups($params);
    $security_group_id = array_shift($result['SecurityGroups'])['GroupId'];
    $this->ec2Service->createTags([
      'Resources' => [$security_group_id],
      'Tags' => [
        [
          'Key' => SecurityGroup::TAG_CREATED_BY_UID,
          'Value' => $this->currentUser->id(),
        ],
      ],
    ]);

    // Create an entity for the new security group.
    $this->ec2Service->updateSecurityGroups(['GroupIds' => [$security_group_id]], FALSE);

    return $vpc_id;
  }

  /**
   * Create a new subnet.
   *
   * @param string $vpc_id
   *   The VPC ID.
   * @param string $cidr_block
   *   The CIDR block of the subnet.
   * @param string $name
   *   The subnet name.
   *
   * @return string
   *   The ID of the subnet created.
   */
  private function createSubnet($vpc_id, $cidr_block, $name) {
    $result = $this->ec2Service->createSubnet([
      'VpcId' => $vpc_id,
      'CidrBlock' => $cidr_block,
    ]);
    if (empty($result['Subnet'])) {
      $this->messenger->addError($this->t(
        'Failed to create a subnet with a CIDR block @cidr_block for the VPC @vpc_id.',
        ['@cidr_block' => $cidr_block, '@vpc_id' => $vpc_id]
      ));
      return NULL;
    }

    $subnet_id = $result['Subnet']['SubnetId'];
    $result = $this->ec2Service->createTags([
      'Resources' => [$subnet_id],
      'Tags' => [
        [
          'Key' => Subnet::TAG_CREATED_BY_UID,
          'Value' => $this->currentUser->id(),
        ],
        [
          'Key' => 'Name',
          'Value' => $name,
        ],
      ],
    ]);

    $this->messenger->addStatus($this->t('The Subnet @subnet_id of the VPC @vpc_id was created for the current login user.', [
      '@subnet_id' => $subnet_id,
      '@vpc_id' => $vpc_id,
    ]));

    return $subnet_id;
  }

  /**
   * Connect the login user own VPC to the system own VPC.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param string $vpc_id
   *   The ID of the login user own VPC.
   *
   * @return bool
   *   The result of the connecting VPCs.
   */
  private function connectUserVpc($cloud_context, $vpc_id) {
    $cloud_config = $this->cloudConfigPluginManager->loadConfigEntity();
    $system_vpc_id = $cloud_config->field_system_vpc->value;

    if (empty($system_vpc_id)) {
      $this->messenger->addError($this->t(
        "The system VPC wasn't set. Please set the system VPC in cloud service provider edit page."
      ));
      return FALSE;
    }

    if ($system_vpc_id === $vpc_id) {
      $this->messenger->addWarning($this->t('The system VPC is the same as the VPC owned by login user, so the peering connection will not be created.'));
      return TRUE;
    }

    return $this->connectVpcs($vpc_id, $system_vpc_id);
  }

  /**
   * Connect two VPCs.
   *
   * @param string $from_vpc_id
   *   The VPC's ID from which to connect.
   * @param string $to_vpc_id
   *   The VPC's ID to which to connect.
   *
   * @return bool
   *   The result of the connecting VPCs.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   The Ec2ServiceException.
   */
  private function connectVpcs($from_vpc_id, $to_vpc_id) {
    // Check if there is a VPC peering connection.
    $params['Filters'] = [
      [
        'Name' => 'requester-vpc-info.vpc-id',
        'Values' => [$from_vpc_id],
      ],
      [
        'Name' => 'accepter-vpc-info.vpc-id',
        'Values' => [$to_vpc_id],
      ],
      [
        'Name' => 'status-code',
        'Values' => ['active'],
      ],
    ];

    $result = $this->ec2Service->describeVpcPeeringConnections($params);
    if (!empty($result['VpcPeeringConnections'])) {
      return TRUE;
    }

    // Create a new VPC peering connection.
    $result = $this->ec2Service->createVpcPeeringConnection([
      'VpcId' => $from_vpc_id,
      'PeerVpcId' => $to_vpc_id,
    ]);
    $connection_id = $result['VpcPeeringConnection']['VpcPeeringConnectionId'];

    // Accept a VPC peering connection request.
    $result = $this->ec2Service->acceptVpcPeeringConnection([
      'VpcPeeringConnectionId' => $connection_id,
    ]);

    if (empty($result['VpcPeeringConnection'])) {
      $this->messenger->addError($this->t('Failed to accept the VPC peering connection request. It is possible due to that two VPCs have overlapping CIDR blocks. Please confirm the CIDR blocks of the VPC @from_vpc_id and @to_vpc_id.', [
        '@from_vpc_id' => $from_vpc_id,
        '@to_vpc_id' => $to_vpc_id,
      ]));
      return FALSE;
    }

    $this->messenger->addStatus($this->t('The VPC peering connection was created from the VPC @from_vpc_id to the VPC @to_vpc_id.', [
      '@from_vpc_id' => $from_vpc_id,
      '@to_vpc_id' => $to_vpc_id,
    ]));

    return TRUE;
  }

  /**
   * Update all cloud server templates in particular cloud region.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateCloudServerTemplateList($cloud_context) {
    $this->ec2Service->setCloudContext($cloud_context);
    $updated = $this->ec2Service->updateCloudServerTemplates();

    return $updated;
  }

}
