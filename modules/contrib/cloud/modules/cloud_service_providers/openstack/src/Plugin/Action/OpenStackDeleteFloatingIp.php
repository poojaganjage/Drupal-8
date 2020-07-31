<?php

namespace Drupal\openstack\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Floating IP deletion form.
 *
 * @Action(
 *   id = "openstack_floating_ip_delete_action",
 *   label = @Translation("Delete Floating IP"),
 *   type = "openstack_floating_ip"
 * )
 */
class OpenStackDeleteFloatingIp extends DeleteAction {

}
