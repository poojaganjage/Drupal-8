<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Cluster Role Binding form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_cluster_role_binding",
 *   label = @Translation("Delete Cluster Role Binding"),
 *   type = "k8s_cluster_role_binding"
 * )
 */
class DeleteClusterRoleBinding extends DeleteAction {

}
