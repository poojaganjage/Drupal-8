<?php

namespace Drupal\aws_cloud\Plugin\QueueWorker;

use Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes for AWs Cloud Update Resources Queue.
 *
 * @QueueWorker(
 *   id = "aws_cloud_update_resources_queue",
 *   title = @Translation("AWS Cloud Update Resources Queue"),
 * )
 */
class AwsCloudUpdateResourcesQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The EC2 service.
   *
   * @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface
   */
  private $ec2Service;

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
   * @param \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface $ec2_service
   *   The EC2 service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   A logger factory instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    array $plugin_definition,
    Ec2ServiceInterface $ec2_service,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->ec2Service = $ec2_service;
    $this->logger = $logger_factory->get('aws_cloud');
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
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $cloud_context = $data['cloud_context'];
    $ec2_method_name = $data['ec2_method_name'];
    $this->ec2Service->setCloudContext($cloud_context);

    if (!method_exists($this->ec2Service, $ec2_method_name)) {
      $this->logger->error("The method $ec2_method_name doesn't exist in class Ec2Service.");
      return;
    }

    $this->ec2Service->$ec2_method_name();
  }

}
