<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Pod form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_pod",
 *   label = @Translation("Delete Pod"),
 *   type = "k8s_pod"
 * )
 */
class DeletePod extends DeleteAction {

}
