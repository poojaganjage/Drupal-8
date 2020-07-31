<?php

namespace Drupal\openstack\Entity;

use Drupal\aws_cloud\Entity\Ec2\SecurityGroupViewBuilder;

/**
 * Provides the OpenStack security group view builders.
 */
class OpenStackSecurityGroupViewBuilder extends SecurityGroupViewBuilder {

  /**
   * Show a default message if not permissions are configured.
   *
   * @param array $build
   *   Build array.
   *
   * @return array
   *   The updated renderable array.
   */
  public function removeIpPermissionsField(array $build) : array {
    /* @var \Drupal\openstack\Entity\OpenStackSecurityGroup $security */
    $security = $build['#openstack_security_group'];

    $inbound = $security->getIpPermission();
    $outbound = $security->getOutboundPermission();
    if ($inbound->count() === 0 && $outbound->count() === 0) {
      unset($build['rules'][0]);
      $build['rules'][] = $this->getNoPermissionMessage($security);
    }
    return $build;
  }

}
