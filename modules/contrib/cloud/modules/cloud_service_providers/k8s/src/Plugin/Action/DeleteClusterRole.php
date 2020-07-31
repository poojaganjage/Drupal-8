<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Cluster Role form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_cluster_role",
 *   label = @Translation("Delete Cluster Role"),
 *   type = "k8s_cluster_role"
 * )
 */
class DeleteClusterRole extends DeleteAction {

}
