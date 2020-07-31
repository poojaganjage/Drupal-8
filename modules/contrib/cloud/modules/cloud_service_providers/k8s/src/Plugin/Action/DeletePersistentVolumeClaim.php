<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Persistent Volume Claim form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_persistent_volume_claim",
 *   label = @Translation("Delete Persistent Volume Claim"),
 *   type = "k8s_persistent_volume_claim"
 * )
 */
class DeletePersistentVolumeClaim extends DeleteAction {

}
