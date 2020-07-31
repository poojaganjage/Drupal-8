<?php

namespace Drupal\k8s\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a Cron Job form.
 *
 * @Action(
 *   id = "entity:delete_action:k8s_cron_job",
 *   label = @Translation("Delete Cron Job"),
 *   type = "k8s_cron_job"
 * )
 */
class DeleteCronJob extends DeleteAction {

}
