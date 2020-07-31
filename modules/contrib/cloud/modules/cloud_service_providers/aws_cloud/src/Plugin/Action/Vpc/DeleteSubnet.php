<?php

namespace Drupal\aws_cloud\Plugin\Action\Vpc;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a VPC form.
 *
 * @Action(
 *   id = "entity:delete_action:aws_cloud_subnet",
 *   label = @Translation("Delete Subnet"),
 *   type = "aws_cloud_subnet"
 * )
 */
class DeleteSubnet extends DeleteAction {

}
