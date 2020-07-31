<?php

namespace Drupal\cloud_budget\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a cloud cost storage form.
 *
 * @Action(
 *   id = "entity:delete_action:cloud_cost_storage",
 *   label = @Translation("Delete Cloud Cost Storage"),
 *   type = "cloud_cost_storage"
 * )
 */
class DeleteCloudCostStorage extends DeleteAction {

}
