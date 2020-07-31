<?php

namespace Drupal\openstack\Form;

use Drupal\aws_cloud\Form\Ec2\ElasticIpDeleteMultipleForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an entities deletion confirmation form.
 */
class OpenStackFloatingIpDeleteMultipleForm extends ElasticIpDeleteMultipleForm {

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

  /**
   * Returns the message to show the user after an item was processed.
   *
   * @param int $count
   *   Count of processed translations.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The item processed message.
   */
  protected function getProcessedMessage($count) {

    $this->ec2Service->updateFloatingIp();
    $this->ec2Service->updateInstances();
    $this->ec2Service->updateNetworkInterfaces();

    return $this->formatPlural($count, 'Deleted @count Floating IP.', 'Deleted @count Floating IPs.');
  }

}
