<?php

namespace Drupal\openstack\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a snapshot deletion form.
 *
 * @Action(
 *   id = "entity:delete_action:openstack_snapshot",
 *   label = @Translation("Delete snapshot"),
 *   type = "openstack_snapshot"
 * )
 */
class OpenStackDeleteSnapshot extends DeleteAction {

}
