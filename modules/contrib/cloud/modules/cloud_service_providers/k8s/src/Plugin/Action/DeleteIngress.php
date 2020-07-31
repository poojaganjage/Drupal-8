<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Ingress form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_ingress",
 *   label = @Translation("Delete Ingress"),
 *   type = "k8s_ingress"
 * )
 */
class DeleteIngress extends DeleteAction {

}
