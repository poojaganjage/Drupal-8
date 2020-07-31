<?php

namespace Drupal\openstack\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a instance deletion form.
 *
 * @Action(
 *   id = "entity:delete_action:openstack_instance",
 *   label = @Translation("Terminate instance"),
 *   type = "openstack_instance"
 * )
 */
class OpenStackDeleteInstance extends DeleteAction {

}
