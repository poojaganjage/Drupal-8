<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a ConfigMap form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_config_map",
 *   label = @Translation("Delete ConfigMap"),
 *   type = "k8s_config_map"
 * )
 */
class DeleteConfigMap extends DeleteAction {

}
