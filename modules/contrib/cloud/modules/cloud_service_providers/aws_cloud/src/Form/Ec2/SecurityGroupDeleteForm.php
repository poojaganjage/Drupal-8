<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for deleting a SecurityGroup entity.
 *
 * @ingroup aws_cloud
 */
class SecurityGroupDeleteForm extends AwsDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $entity = $this->entity;
    $this->ec2Service->setCloudContext($entity->getCloudContext());

    if ($this->ec2Service->deleteSecurityGroup([
      'GroupId'   => $entity->getGroupId(),
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
