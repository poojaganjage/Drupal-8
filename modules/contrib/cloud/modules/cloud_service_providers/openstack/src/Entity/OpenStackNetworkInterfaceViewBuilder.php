<?php

namespace Drupal\openstack\Entity;

use Drupal\aws_cloud\Entity\Ec2\NetworkInterfaceViewBuilder;

/**
 * Provides the network interface view builders.
 */
class OpenStackNetworkInterfaceViewBuilder extends NetworkInterfaceViewBuilder {

  /**
   * Show a default message if attachments are not available.
   *
   * @param array $build
   *   Build array.
   *
   * @return array
   *   The updated renderable array.
   */
  public function removeAttachmentField(array $build) {
    /* @var \Drupal\openstack\Entity\NetworkInterface $network_interface */
    $network_interface =& $build['attachment'][0]['#openstack_network_interface'];

    $attachmanet_id = $network_interface->getAttachmentId();
    $attachmanet_owner = $network_interface->getAttachmentOwner() ?? '';
    $attachmanet_status = $network_interface->getAttachmentStatus() ?? '';

    if (empty($attachmanet_id) && empty($attachmanet_owner) && empty($attachmanet_status)) {
      unset($build['attachment']);
    }
    return $build;
  }

}
