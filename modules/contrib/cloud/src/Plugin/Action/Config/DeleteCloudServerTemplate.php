<?php

namespace Drupal\cloud\Plugin\Action\Config;

use Drupal\Core\Action\Plugin\Action\DeleteAction;
use Drupal\Core\Session\AccountInterface;

/**
 * Redirects to a cloud server template deletion form.
 *
 * @Action(
 *   id = "entity:delete_action:cloud_server_template",
 *   label = @Translation("Delete launch template"),
 *   type = "cloud_server_template"
 * )
 */
class DeleteCloudServerTemplate extends DeleteAction {

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\user\UserInterface $object */
    return $object->access($this->getOperation(), $account, $return_as_object);
  }

  /**
   * {@inheritdoc}
   */
  protected function getOperation() {
    return 'delete';
  }

}
