<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Deployment form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_replica_set",
 *   label = @Translation("Delete Replica Set"),
 *   type = "k8s_replica_set"
 * )
 */
class DeleteReplicaSet extends DeleteAction {

}
