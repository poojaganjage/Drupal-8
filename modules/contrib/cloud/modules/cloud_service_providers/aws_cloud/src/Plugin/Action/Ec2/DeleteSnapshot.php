<?php

namespace Drupal\aws_cloud\Plugin\Action\Ec2;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a snapshot deletion form.
 *
 * @Action(
 *   id = "entity:delete_action:aws_cloud_snapshot",
 *   label = @Translation("Delete snapshot"),
 *   type = "aws_cloud_snapshot"
 * )
 */
class DeleteSnapshot extends DeleteAction {

}
