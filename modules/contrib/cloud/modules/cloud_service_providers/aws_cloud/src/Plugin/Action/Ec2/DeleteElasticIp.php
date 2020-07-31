<?php

namespace Drupal\aws_cloud\Plugin\Action\Ec2;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to an Elastic IP deletion form.
 *
 * @Action(
 *   id = "aws_cloud_elastic_ip_delete_action",
 *   label = @Translation("Delete Elastic IP"),
 *   type = "aws_cloud_elastic_ip"
 * )
 */
class DeleteElasticIp extends DeleteAction {

}
