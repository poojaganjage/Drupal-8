<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Job form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_job",
 *   label = @Translation("Delete Job"),
 *   type = "k8s_job"
 * )
 */
class DeleteJob extends DeleteAction {

}
