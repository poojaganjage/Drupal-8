<?php

namespace Drupal\aws_cloud\Form\Vpc;

use Drupal\aws_cloud\Form\Ec2\AwsDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for deleting a VPC Peering Connection entity.
 *
 * @ingroup aws_cloud
 */
class VpcPeeringConnectionDeleteForm extends AwsDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $entity = $this->entity;
    $this->ec2Service->setCloudContext($entity->getCloudContext());

    if ($this->ec2Service->deleteVpcPeeringConnection([
      'VpcPeeringConnectionId' => $entity->getVpcPeeringConnectionId(),
    ]) !== NULL) {

      $entity->delete();

      $this->messenger->addStatus($this->getDeletionMessage());
      $this->logDeletionMessage();
      $this->clearCacheValues();
    }
    else {
      $this->processOperationErrorStatus($entity, 'deleted');
    }

    $form_state->setRedirect('view.aws_cloud_vpc_peering_connection.list', ['cloud_context' => $entity->getCloudContext()]);
  }

}
