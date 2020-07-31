<?php

namespace Drupal\cloud\Plugin\Action\Config;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a cloud service provider (CloudConfig) deletion form.
 *
 * @Action(
 *   id = "entity:delete_action:cloud_config",
 *   label = @Translation("Cloud Service Provider"),
 *   type = "cloud_config"
 * )
 */
class DeleteCloudConfig extends DeleteAction {

}
