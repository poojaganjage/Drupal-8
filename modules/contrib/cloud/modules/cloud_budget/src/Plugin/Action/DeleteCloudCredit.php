<?php

namespace Drupal\cloud_budget\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a cloud credit form.
 *
 * @Action(
 *   id = "entity:delete_action:cloud_credit",
 *   label = @Translation("Delete Credit"),
 *   type = "cloud_credit"
 * )
 */
class DeleteCloudCredit extends DeleteAction {

}
