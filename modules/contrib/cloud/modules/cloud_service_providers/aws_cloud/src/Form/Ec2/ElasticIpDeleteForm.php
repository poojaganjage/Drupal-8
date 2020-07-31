<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for deleting a ElasticIp entity.
 *
 * @ingroup aws_cloud
 */
class ElasticIpDeleteForm extends AwsDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /* @var \Drupal\aws_cloud\Entity\Ec2\ElasticIp $entity */
    $entity = $this->entity;
    $this->ec2Service->setCloudContext($entity->getCloudContext());

    $allocation_id = $entity->getAllocationId();
    $public_ip = $entity->getPublicIp();
    $params = [];
    if ($entity->getDomain() === 'standard' && !empty($public_ip)) {
      $params['PublicIp'] = $public_ip;
    }
    elseif ($entity->getDomain() === 'vpc' && !empty($allocation_id)) {
      $params['AllocationId'] = $allocation_id;
    }
    if (!empty($entity)
    && !empty($params)
    && !empty($this->ec2Service->releaseAddress($params))) {

      // Update instances after the Elastic IP is deleted.
      if ($entity->getEntityTypeId() === 'aws_cloud_elastic_ip') {
        $this->ec2Service->updateInstances();
      }

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
