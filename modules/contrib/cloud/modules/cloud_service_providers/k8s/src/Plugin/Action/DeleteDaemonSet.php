<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Daemon Set form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_daemon_set",
 *   label = @Translation("Delete Daemon Set"),
 *   type = "k8s_daemon_set"
 * )
 */
class DeleteDaemonSet extends DeleteAction {

}
