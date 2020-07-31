<?php

namespace Drupal\openstack\Plugin\Action;

use Drupal\aws_cloud\Plugin\Action\Ec2\StartInstance;

/**
 * Start selected Instance(s).
 *
 * @Action(
 *   id = "openstack_instance_start_action",
 *   label = @Translation("Start Instance"),
 *   type = "openstack_instance",
 *   confirm_form_route_name
 *     = "entity.openstack_instance.start_multiple_form"
 * )
 */
class OpenStackStartInstance extends StartInstance {

}
