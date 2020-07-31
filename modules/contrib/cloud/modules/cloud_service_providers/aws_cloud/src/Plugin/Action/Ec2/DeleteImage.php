<?php

namespace Drupal\aws_cloud\Plugin\Action\Ec2;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to an image deletion form.
 *
 * @Action(
 *   id = "entity:delete_action:aws_cloud_image",
 *   label = @Translation("Delete image"),
 *   type = "aws_cloud_image"
 * )
 */
class DeleteImage extends DeleteAction {

}
