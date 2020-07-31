<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Namespace form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_namespace",
 *   label = @Translation("Delete Namespace"),
 *   type = "k8s_namespace"
 * )
 */
class DeleteNamespace extends DeleteAction {

}
