<?php

namespace Drupal\aws_cloud\Plugin\Action\Vpc;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a VPC Peering Connection form.
 *
 * @Action(
 *   id = "entity:delete_action:aws_cloud_vpc_peering_connection",
 *   label = @Translation("Delete VPC Peering Connection"),
 *   type = "aws_cloud_vpc_peering_connection"
 * )
 */
class DeleteVpcPeeringConnection extends DeleteAction {

}
