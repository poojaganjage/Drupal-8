<?php

namespace Drupal\cloud\Plugin\Action\Config;

use Drupal\Core\Action\Plugin\Action\DeleteAction;
use Drupal\Core\Session\AccountInterface;

/**
 * Redirects to a cloud project deletion form.
 *
 * @Action(
 *   id = "entity:delete_action:cloud_project",
 *   label = @Translation("Delete cloud project"),
 *   type = "cloud_project"
 * )
 */
class DeleteCloudProject extends DeleteAction {

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
