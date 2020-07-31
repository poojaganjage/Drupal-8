<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for deleting a Snapshot entity.
 *
 * @ingroup aws_cloud
 */
class SnapshotDeleteForm extends AwsDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $entity = $this->entity;
    $this->ec2Service->setCloudContext($entity->getCloudContext());

    if ($this->ec2Service->deleteSnapshot([
      'SnapshotId' => $entity->getSnapshotId(),
    ]) !== NULL) {

      $entity->delete();

      $this->messenger->addStatus($this->getDeletionMessage());
      $this->logDeletionMessage();
      $this->clearCacheValues();
    }
    else {

      $this->processOperationErrorStatus($entity, 'deleted');
    }

    $form_state->setRedirect("view.{$entity->getEntityTypeId()}.list", ['cloud_context' => $entity->getCloudContext()]);
  }

}
