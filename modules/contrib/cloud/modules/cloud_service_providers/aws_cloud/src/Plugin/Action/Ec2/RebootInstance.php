<?php

namespace Drupal\aws_cloud\Plugin\Action\Ec2;

use Drupal\aws_cloud\Plugin\Action\OperateAction;

/**
 * Reboot selected Instance(s).
 *
 * @Action(
 *   id = "aws_cloud_instance_reboot_action",
 *   label = @Translation("Reboot Instance"),
 *   type = "aws_cloud_instance",
 *   confirm_form_route_name
 *     = "entity.aws_cloud_instance.reboot_multiple_form"
 * )
 */
class RebootInstance extends OperateAction {

  /**
   * {@inheritdoc}
   */
  protected function getOperation() {
    return 'reboot';
  }

}
