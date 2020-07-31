<?php

namespace Drupal\openstack\Plugin\cloud\config;

use Drupal\aws_cloud\Plugin\cloud\config\AwsCloudConfigPlugin;

/**
 * OpenStack cloud service provider (CloudConfig) plugin class.
 */
class OpenStackCloudConfigPlugin extends AwsCloudConfigPlugin {

  /**
   * {@inheritdoc}
   */
  public function getInstanceCollectionTemplateName() {
    return 'view.openstack_instance.list';
  }

  /**
   * {@inheritdoc}
   */
  public function loadCredentials($cloud_context) {
    /* @var \Drupal\cloud\Entity\CloudConfig $entity */
    $entity = $this->loadConfigEntity($cloud_context);
    $credentials = [];
    if ($entity !== FALSE) {
      $credentials['region'] = $entity->get('field_os_region')->value;
      $credentials['endpoint'] = $entity->get('field_api_endpoint')->value;
      $credentials['ini_file'] = $this->fileSystem->realpath(aws_cloud_ini_file_path($entity->get('cloud_context')->value));
      // todo: this is hard-coded.
      $credentials['version'] = 'latest';
    }

    return $credentials;
  }

  /**
   * {@inheritdoc}
   */
  public function getPricingPageRoute() {
    return '';
  }

}
