<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Deployment form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_deployment",
 *   label = @Translation("Delete Deployment"),
 *   type = "k8s_deployment"
 * )
 */
class DeleteDeployment extends DeleteAction {

}
