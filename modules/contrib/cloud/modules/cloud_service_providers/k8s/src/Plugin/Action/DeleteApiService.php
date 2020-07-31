<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a API Service form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_api_service",
 *   label = @Translation("Delete API Service"),
 *   type = "k8s_api_service"
 * )
 */
class DeleteApiService extends DeleteAction {

}
