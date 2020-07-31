<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Stateful Set form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_stateful_set",
 *   label = @Translation("Delete Stateful Set"),
 *   type = "k8s_stateful_set"
 * )
 */
class DeleteStatefulSet extends DeleteAction {

}
