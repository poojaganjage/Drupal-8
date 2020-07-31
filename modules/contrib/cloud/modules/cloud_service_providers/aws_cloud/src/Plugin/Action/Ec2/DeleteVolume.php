<?php

namespace Drupal\aws_cloud\Plugin\Action\Ec2;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a volume deletion form.
 *
 * @Action(
 *   id = "entity:delete_action:aws_cloud_volume",
 *   label = @Translation("Delete volume"),
 *   type = "aws_cloud_volume"
 * )
 */
class DeleteVolume extends DeleteAction {

}
