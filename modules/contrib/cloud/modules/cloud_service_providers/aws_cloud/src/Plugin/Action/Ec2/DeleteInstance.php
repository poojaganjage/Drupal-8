<?php

namespace Drupal\aws_cloud\Plugin\Action\Ec2;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a instance deletion form.
 *
 * @Action(
 *   id = "entity:delete_action:aws_cloud_instance",
 *   label = @Translation("Terminate instance"),
 *   type = "aws_cloud_instance"
 * )
 */
class DeleteInstance extends DeleteAction {

}
