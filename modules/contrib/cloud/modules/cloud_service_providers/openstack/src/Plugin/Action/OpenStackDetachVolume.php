<?php

namespace Drupal\openstack\Plugin\Action;

use Drupal\aws_cloud\Plugin\Action\OperateAction;

/**
 * Redirects to a volume detach form.
 *
 * @Action(
 *   id = "openstack_volume_detach_action",
 *   label = @Translation("Detach volume"),
 *   type = "openstack_volume",
 *   confirm_form_route_name
 *     = "entity.openstack_volume.detach_multiple_form"
 * )
 */
class OpenStackDetachVolume extends OperateAction {

  /**
   * {@inheritdoc}
   */
  protected function getOperation() {
    return 'detach';
  }

}
