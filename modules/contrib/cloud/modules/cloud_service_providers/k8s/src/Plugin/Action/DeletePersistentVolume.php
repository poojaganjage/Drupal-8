<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Persistent Volume form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_persistent_volume",
 *   label = @Translation("Delete persistent volume"),
 *   type = "k8s_persistent_volume"
 * )
 */
class DeletePersistentVolume extends DeleteAction {

}
