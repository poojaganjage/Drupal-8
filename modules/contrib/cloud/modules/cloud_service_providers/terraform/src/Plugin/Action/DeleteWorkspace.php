<?php

namespace Drupal\terraform\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Pod form.
 *
 * @Action(
 *   id = "entity:delete_action:terraform_workspace",
 *   label = @Translation("Delete Workspace"),
 *   type = "terraform_workspace"
 * )
 */
class DeleteWorkspace extends DeleteAction {

}
