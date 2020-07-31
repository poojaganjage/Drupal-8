<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Service form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_service",
 *   label = @Translation("Delete Service"),
 *   type = "k8s_service"
 * )
 */
class DeleteService extends DeleteAction {

}
