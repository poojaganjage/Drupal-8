<?php

namespace Drupal\openstack\Form;

use Drupal\aws_cloud\Form\Ec2\SecurityGroupDeleteMultipleForm;
use Drupal\cloud\Traits\CloudDeleteMultipleFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an entities deletion confirmation form.
 */
class OpenStackSecurityGroupDeleteMultipleForm extends SecurityGroupDeleteMultipleForm {

  use CloudDeleteMultipleFormTrait;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('tempstore.private'),
      $container->get('messenger'),
      $container->get('plugin.manager.cloud_config_plugin'),
      $container->get('openstack.ec2')
    );

  }

}
