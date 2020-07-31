<?php

namespace Drupal\aws_cloud\Plugin\Action\Ec2;

use Drupal\aws_cloud\Plugin\Action\OperateAction;

/**
 * Disassociate selected Elastic IP(s).
 *
 * @Action(
 *   id = "aws_cloud_elastic_ip_disassociate_action",
 *   label = @Translation("Disassociate Elastic IP"),
 *   type = "aws_cloud_elastic_ip",
 *   confirm_form_route_name
 *     = "entity.aws_cloud_elastic_ip.disassociate_multiple_form"
 * )
 */
class DisassociateElasticIp extends OperateAction {

  /**
   * {@inheritdoc}
   */
  protected function getOperation() {
    return 'disassociate';
  }

}
