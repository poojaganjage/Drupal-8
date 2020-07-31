<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Service Account form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_service_account",
 *   label = @Translation("Delete Service Account"),
 *   type = "k8s_service_account"
 * )
 */
class DeleteServiceAccount extends DeleteAction {

}
