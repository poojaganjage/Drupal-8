<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Endpoint form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_endpoint",
 *   label = @Translation("Delete Endpoint"),
 *   type = "k8s_endpoint"
 * )
 */
class DeleteEndpoint extends DeleteAction {

}
