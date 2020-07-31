<?php

namespace Drupal\openstack\Plugin\Action;

use Drupal\aws_cloud\Plugin\Action\Ec2\StopInstance;

/**
 * Stop selected Instance(s).
 *
 * @Action(
 *   id = "openstack_instance_stop_action",
 *   label = @Translation("Stop Instance"),
 *   type = "openstack_instance",
 *   confirm_form_route_name
 *     = "entity.openstack_instance.stop_multiple_form"
 * )
 */
class OpenStackStopInstance extends StopInstance {

}
