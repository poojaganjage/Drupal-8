<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Limit Range form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_limit_range",
 *   label = @Translation("Delete Limit Range"),
 *   type = "k8s_limit_range"
 * )
 */
class DeleteLimitRange extends DeleteAction {

}
