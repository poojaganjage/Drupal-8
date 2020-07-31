<?php

namespace Drupal\aws_cloud\Plugin\Action\Ec2;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Network Interface form.
 *
 * @Action(
 *   id = "entity:delete_action:aws_cloud_network_interface",
 *   label = @Translation("Delete Network Interface"),
 *   type = "aws_cloud_network_interface"
 * )
 */
class DeleteNetworkInterface extends DeleteAction {

}
