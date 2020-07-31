<?php

namespace Drupal\aws_cloud\Plugin\Action\Vpc;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a VPC form.
 *
 * @Action(
 *   id = "entity:delete_action:aws_cloud_vpc",
 *   label = @Translation("Delete Vpc"),
 *   type = "aws_cloud_vpc"
 * )
 */
class DeleteVpc extends DeleteAction {

}
