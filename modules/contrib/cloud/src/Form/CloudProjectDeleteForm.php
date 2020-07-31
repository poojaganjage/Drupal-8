<?php

namespace Drupal\cloud\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for deleting cloud project entities.
 *
 * @ingroup cloud_project
 */
class CloudProjectDeleteForm extends ContentEntityDeleteForm {

  /**
   * Form submission handler.
   *
   * Need to override this submitForm method in order
   * to avoid duplicated status messages for the deletion
   * while handling our own custom status messages
   * as nested bullet items.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // We don't call parent::submitForm
    // since we don't want to show a status message here.
    // See also the status message display logic
    // at the function k8s_cloud_project_delete() in k8s.module.
    $entity = $this->entity;
    $entity->delete();
    $this->logDeletionMessage();
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
