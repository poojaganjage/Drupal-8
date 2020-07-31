<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Role Binding form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_role_binding",
 *   label = @Translation("Delete Role Binding"),
 *   type = "k8s_role_binding"
 * )
 */
class DeleteRoleBinding extends DeleteAction {

}
