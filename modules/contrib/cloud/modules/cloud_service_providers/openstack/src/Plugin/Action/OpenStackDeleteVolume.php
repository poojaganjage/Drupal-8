<?php

namespace Drupal\openstack\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a volume deletion form.
 *
 * @Action(
 *   id = "entity:delete_action:openstack_volume",
 *   label = @Translation("Delete volume"),
 *   type = "openstack_volume"
 * )
 */
class OpenStackDeleteVolume extends DeleteAction {

}
