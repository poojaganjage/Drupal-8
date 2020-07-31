<?php

namespace Drupal\aws_cloud\Plugin\Action\Ec2;

use Drupal\aws_cloud\Plugin\Action\OperateAction;

/**
 * Stop selected Instance(s).
 *
 * @Action(
 *   id = "aws_cloud_instance_stop_action",
 *   label = @Translation("Stop Instance"),
 *   type = "aws_cloud_instance",
 *   confirm_form_route_name
 *     = "entity.aws_cloud_instance.stop_multiple_form"
 * )
 */
class StopInstance extends OperateAction {

  /**
   * {@inheritdoc}
   */
  protected function getOperation() {
    return 'stop';
  }

}
