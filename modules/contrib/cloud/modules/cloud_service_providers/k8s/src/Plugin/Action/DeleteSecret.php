<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Secret form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_secret",
 *   label = @Translation("Delete Secret"),
 *   type = "k8s_secret"
 * )
 */
class DeleteSecret extends DeleteAction {

}
