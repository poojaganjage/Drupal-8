<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Form\FormStateInterface;

/**
 * Stops an AWS Instance.
 *
 * This form confirms with the user if they really
 * want to stop the instance.
 *
 * @package Drupal\aws_cloud\Form\Ec2
 */
class InstanceStopForm extends AwsDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $entity = $this->entity;

    return $this->t('Are you sure you want to stop instance: %name?', [
      '%name' => $entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Stop');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $entity = $this->entity;

    $params = [
      'InstanceIds' => [
        $entity->getInstanceId(),
      ],
    ];

    $this->ec2Service->setCloudContext($entity->getCloudContext());
    $result = $this->ec2Service->stopInstances($params);
    if (!empty($result)) {
      try {
        $current_state = $result['StoppingInstances'][0]['CurrentState']['Name'];
        $instance = $this->entityTypeManager
          ->getStorage($entity->getEntityTypeId())
          ->load($entity->id());
        $instance->setInstanceState($current_state);
        $instance->save();

        $this->processOperationStatus($instance, 'stopped');

        $this->clearCacheValues();
      }
      catch (\Exception $e) {
        $this->handleException($e);
      }
    }

    $form_state->setRedirect("view.{$entity->getEntityTypeId()}.list", ['cloud_context' => $entity->getCloudContext()]);

  }

}
