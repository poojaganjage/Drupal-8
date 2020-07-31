<?php

namespace Drupal\aws_cloud\Commands\aws;

use Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drush\Commands\DrushCommands;

/**
 * Provides drush commands for instance.
 */
class InstanceCommands extends DrushCommands {

  /**
   * The Ec2Service.
   *
   * @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface
   */
  protected $ec2Service;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * InstanceCommands constructor.
   *
   * @param \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface $ec2_service
   *   The Ec2Service.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   */
  public function __construct(Ec2ServiceInterface $ec2_service, CloudConfigPluginManagerInterface $cloud_config_plugin_manager) {
    $this->ec2Service = $ec2_service;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
  }

  /**
   * Terminates expired instances.
   *
   * @command aws_cloud:terminate_instances
   *
   * @aliases aws-ti
   */
  public function drushInstanceTerminate() {
    $entities = $this->cloudConfigPluginManager->loadConfigEntities('aws_cloud');
    foreach ($entities ?: [] as $entity) {
      /* @var \Drupal\cloud\Entity\CloudConfig $entity */
      $this->ec2Service->setCloudContext($entity->getCloudContext());
      $instances = aws_cloud_get_expired_instances($entity->getCloudContext());
      if ($instances) {
        $this->output()->writeln('Terminating the following instances ' . implode(',', $instances['InstanceIds']));
        $this->ec2Service->terminateInstance($instances);
        $this->ec2Service->updateInstances();
      }
    }
  }

}
