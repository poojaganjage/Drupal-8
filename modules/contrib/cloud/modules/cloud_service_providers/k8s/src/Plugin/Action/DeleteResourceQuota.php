<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Resource Quota form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_resource_quota",
 *   label = @Translation("Delete Resource Quota"),
 *   type = "k8s_resource_quota"
 * )
 */
class DeleteResourceQuota extends DeleteAction {

}
