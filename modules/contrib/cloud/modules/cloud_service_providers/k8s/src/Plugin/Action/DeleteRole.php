<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Role form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_role",
 *   label = @Translation("Delete Role"),
 *   type = "k8s_role"
 * )
 */
class DeleteRole extends DeleteAction {

}
