<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for deleting a Instance entity.
 *
 * @ingroup aws_cloud
 */
class InstanceDeleteForm extends AwsDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {

    return $this->t('Delete | Terminate');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $entity = $this->entity;
    $this->ec2Service->setCloudContext($entity->getCloudContext());

    $result = $this->ec2Service->terminateInstance([
      'InstanceIds' => [$entity->getInstanceId()],
    ]);

    if (isset($result['TerminatingInstances'][0]['InstanceId'])) {

      $entity->delete();

      $this->messenger->addStatus($this->getDeletionMessage());
      $this->logDeletionMessage();
      $this->clearCacheValues();

      $form_state->setRedirect("view.{$entity->getEntityTypeId()}.list", ['cloud_context' => $entity->getCloudContext()]);
    }
    else {

      $this->processOperationErrorStatus($entity, 'deleted');
    }

    $this->ec2Service->updateInstances();

    $form_state->setRedirect("view.{$entity->getEntityTypeId()}.list", ['cloud_context' => $entity->getCloudContext()]);
  }

}
