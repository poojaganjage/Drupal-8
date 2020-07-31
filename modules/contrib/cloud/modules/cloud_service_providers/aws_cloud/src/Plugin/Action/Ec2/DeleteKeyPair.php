<?php

namespace Drupal\aws_cloud\Plugin\Action\Ec2;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Key Pair form.
 *
 * @Action(
 *   id = "entity:delete_action:aws_cloud_key_pair",
 *   label = @Translation("Delete Key Pair"),
 *   type = "aws_cloud_key_pair"
 * )
 */
class DeleteKeyPair extends DeleteAction {

}
