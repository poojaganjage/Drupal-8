<?php

namespace Drupal\aws_cloud\Form\Vpc;

use Drupal\aws_cloud\Form\Ec2\AwsDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for deleting a VPC entity.
 *
 * @ingroup aws_cloud
 */
class VpcDeleteForm extends AwsDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $entity = $this->entity;
    $this->ec2Service->setCloudContext($entity->getCloudContext());

    // Delete flow log.
    aws_cloud_delete_flow_log($entity->getCloudContext(), $entity->getVpcId());

    if ($this->ec2Service->deleteVpc([
      'VpcId' => $entity->getVpcId(),
    ]) !== NULL) {

      $entity->delete();

      $this->messenger->addStatus($this->getDeletionMessage());
      $this->logDeletionMessage();
      $this->clearCacheValues();
    }
    else {

      $this->processOperationErrorStatus($entity, 'deleted');
    }

    $form_state->setRedirect('view.aws_cloud_vpc.list', ['cloud_context' => $entity->getCloudContext()]);
  }

}
