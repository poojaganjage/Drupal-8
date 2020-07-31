<?php

namespace Drupal\openstack\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a SECURITY_GROUP form.
 *
 * @Action(
 *   id = "entity:delete_action:openstack_security_group",
 *   label = @Translation("Delete Security Group"),
 *   type = "openstack_security_group"
 * )
 */
class OpenStackDeleteSecurityGroup extends DeleteAction {

}
