<?php

namespace Drupal\openstack\Plugin\Menu\LocalAction;

use Drupal\Core\Menu\LocalActionDefault;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * The local action for OpenStackSnapshotAddForm.
 */
class OpenStackSnapshotAddFormLocalAction extends LocalActionDefault {

  /**
   * {@inheritdoc}
   */
  public function getRouteParameters(RouteMatchInterface $route_match) {
    $parameters = parent::getRouteParameters($route_match);

    // Add volume_id parameter.
    if ($route_match->getRouteName() === 'entity.openstack_volume.edit_form'
      || $route_match->getRouteName() === 'entity.openstack_volume.canonical') {

      $volume = $route_match->getParameter('openstack_volume');
      if ($volume !== NULL) {
        $parameters['volume_id'] = $volume->getVolumeId();
      }
    }

    return $parameters;
  }

}
