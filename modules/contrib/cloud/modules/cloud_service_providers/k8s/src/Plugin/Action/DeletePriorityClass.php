<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Priority Class form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_priority_class",
 *   label = @Translation("Delete Priority Class"),
 *   type = "k8s_priority_class"
 * )
 */
class DeletePriorityClass extends DeleteAction {

}
