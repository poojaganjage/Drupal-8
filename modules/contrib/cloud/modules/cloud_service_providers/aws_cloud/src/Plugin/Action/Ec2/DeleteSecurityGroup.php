<?php

namespace Drupal\aws_cloud\Plugin\Action\Ec2;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a SECURITY_GROUP form.
 *
 * @Action(
 *   id = "entity:delete_action:aws_cloud_security_group",
 *   label = @Translation("Delete Security Group"),
 *   type = "aws_cloud_security_group"
 * )
 */
class DeleteSecurityGroup extends DeleteAction {

}
