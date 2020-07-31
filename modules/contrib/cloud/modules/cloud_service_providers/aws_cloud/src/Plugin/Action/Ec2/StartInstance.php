<?php

namespace Drupal\aws_cloud\Plugin\Action\Ec2;

use Drupal\aws_cloud\Plugin\Action\OperateAction;

/**
 * Start selected Instance(s).
 *
 * @Action(
 *   id = "aws_cloud_instance_start_action",
 *   label = @Translation("Start Instance"),
 *   type = "aws_cloud_instance",
 *   confirm_form_route_name
 *     = "entity.aws_cloud_instance.start_multiple_form"
 * )
 */
class StartInstance extends OperateAction {

  /**
   * {@inheritdoc}
   */
  protected function getOperation() {
    return 'start';
  }

}
