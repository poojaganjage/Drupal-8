<?php

namespace Drupal\aws_cloud\Plugin\Action\Ec2;

use Drupal\aws_cloud\Plugin\Action\OperateAction;

/**
 * Redirects to a volume detach form.
 *
 * @Action(
 *   id = "aws_cloud_volume_detach_action",
 *   label = @Translation("Detach volume"),
 *   type = "aws_cloud_volume",
 *   confirm_form_route_name
 *     = "entity.aws_cloud_volume.detach_multiple_form"
 * )
 */
class DetachVolume extends OperateAction {

  /**
   * {@inheritdoc}
   */
  protected function getOperation() {
    return 'detach';
  }

}
