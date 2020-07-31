<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Network Policy form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_network_policy",
 *   label = @Translation("Delete Network Policy"),
 *   type = "k8s_network_policy"
 * )
 */
class DeleteNetworkPolicy extends DeleteAction {

}
