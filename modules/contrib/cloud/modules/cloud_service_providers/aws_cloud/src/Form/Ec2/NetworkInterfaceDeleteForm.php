<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for deleting a NetworkInterface entity.
 *
 * @ingroup aws_cloud
 */
class NetworkInterfaceDeleteForm extends AwsDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    /* @var $entity \Drupal\aws_cloud\Entity\Ec2\NetworkInterface */
    $entity = $this->entity;

    $this->ec2Service->setCloudContext($entity->getCloudContext());

    if ($this->ec2Service->deleteNetworkInterface([
      'NetworkInterfaceId' => $entity->getNetworkInterfaceId(),
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
