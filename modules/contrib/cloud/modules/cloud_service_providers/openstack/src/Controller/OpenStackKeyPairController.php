<?php

namespace Drupal\openstack\Controller;

use Drupal\aws_cloud\Controller\Ec2\AwsCloudKeyPairController;

/**
 * Controller responsible for OpenStack KeyPair.
 */
class OpenStackKeyPairController extends AwsCloudKeyPairController {

  /**
   * Download Key.
   *
   * @param string $cloud_context
   *   Cloud context string.
   * @param object $key_pair
   *   OpenStack KeyPair.
   * @param string $entity_type
   *   The entity type, such as cloud_server_template.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A binary file response object or redirect if key file doesn't exist.
   */
  public function downloadKey($cloud_context, $key_pair, $entity_type = 'aws_cloud') {
    return parent::downloadKey($cloud_context, $key_pair, 'openstack');
  }

}
