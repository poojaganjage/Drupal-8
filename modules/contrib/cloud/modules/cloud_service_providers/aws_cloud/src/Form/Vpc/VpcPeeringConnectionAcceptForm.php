<?php

namespace Drupal\aws_cloud\Form\Vpc;

use Drupal\aws_cloud\Form\Ec2\AwsDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Accept an AWS VPC Peering Connection.
 */
class VpcPeeringConnectionAcceptForm extends AwsDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $entity = $this->entity;

    return $this->t('Are you sure you want to accept vpc peering connection: %name?', [
      '%name' => $entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Accept');
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If acceptVpcPeeringConnection has some error.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $entity = $this->entity;

    $this->ec2Service->setCloudContext($this->entity->getCloudContext());
    $result = $this->ec2Service->acceptVpcPeeringConnection([
      'VpcPeeringConnectionId' => $this->entity->getVpcPeeringConnectionId(),
    ]);

    if (empty($result['VpcPeeringConnection'])) {
      $this->processOperationErrorStatus($entity, 'accepted');
    }
    else {
      // Update the vpc.
      $this->ec2Service->updateVpcPeeringConnections([
        'VpcPeeringConnectionIds' => [$this->entity->getVpcPeeringConnectionId()],
      ], FALSE);

      $this->processOperationStatus($entity, 'accepted');
    }

    $this->clearCacheValues();
    $form_state->setRedirect("view.{$this->entity->getEntityTypeId()}.list", ['cloud_context' => $this->entity->getCloudContext()]);
  }

}
