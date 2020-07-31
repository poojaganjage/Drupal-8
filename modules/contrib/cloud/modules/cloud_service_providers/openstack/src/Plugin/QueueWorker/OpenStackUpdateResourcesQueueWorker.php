<?php

namespace Drupal\openstack\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\openstack\Service\OpenStackEc2Service;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes for Openstack Update Resources Queue.
 *
 * @QueueWorker(
 *   id = "openstack_update_resources_queue",
 *   title = @Translation("Openstack Update Resources Queue"),
 * )
 */
class OpenStackUpdateResourcesQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The EC2 service.
   *
   * @var \Drupal\openstack\Service\OpenStackEc2Service
   */
  private $openStackEc2Service;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new LocaleTranslation object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\openstack\Service\OpenStackEc2Service $openstack_ec2_service
   *   The Openstack EC2 service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   A logger factory instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    array $plugin_definition,
    OpenStackEc2Service $openstack_ec2_service,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->openStackEc2Service = $openstack_ec2_service;
    $this->logger = $logger_factory->get('openstack');
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
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $cloud_context = $data['cloud_context'];
    $openstack_ec2_method_name = $data['openstack_ec2_method_name'];
    $this->openStackEc2Service->setCloudContext($cloud_context);

    if (!method_exists($this->openStackEc2Service, $openstack_ec2_method_name)) {
      $this->logger->error("The method $openstack_ec2_method_name doesn't exist in class OpenStackEc2Service.");
      return;
    }

    $this->openStackEc2Service->$openstack_ec2_method_name();
  }

}
