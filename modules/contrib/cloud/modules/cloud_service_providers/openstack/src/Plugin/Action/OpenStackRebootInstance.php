<?php

namespace Drupal\openstack\Plugin\Action;

use Drupal\aws_cloud\Plugin\Action\Ec2\RebootInstance;

/**
 * Reboot selected Instance(s).
 *
 * @Action(
 *   id = "openstack_instance_reboot_action",
 *   label = @Translation("Reboot Instance"),
 *   type = "openstack_instance",
 *   confirm_form_route_name
 *     = "entity.openstack_instance.reboot_multiple_form"
 * )
 */
class OpenStackRebootInstance extends RebootInstance {

}
