<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Storage Class form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_storage_class",
 *   label = @Translation("Delete Storage Class"),
 *   type = "k8s_storage_class"
 * )
 */
class DeleteStorageClass extends DeleteAction {

}
