<?php

namespace Drupal\terraform\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Pod form.
 *
 * @Action(
 *   id = "entity:delete_action:terraform_variable",
 *   label = @Translation("Delete Variable"),
 *   type = "terraform_variable"
 * )
 */
class DeleteVariable extends DeleteAction {

}
