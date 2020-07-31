<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Form\FormStateInterface;

/**
 * Starts a stopped AWS Instance.
 *
 * @package Drupal\aws_cloud\Form\Ec2
 */
class InstanceStartForm extends AwsDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $entity = $this->entity;

    return $this->t('Are you sure you want to start instance: %name?', [
      '%name' => $entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Start');
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
    $result = $this->ec2Service->startInstances($params);
    if (!empty($result)) {
      try {
        $current_state = $result['StartingInstances'][0]['CurrentState']['Name'];
        $instance = $this->entityTypeManager
          ->getStorage($entity->getEntityTypeId())
          ->load($entity->id());
        $instance->setInstanceState($current_state);
        $instance->save();

        $this->processOperationStatus($instance, 'started');
        $this->clearCacheValues();
      }
      catch (\Exception $e) {
        $this->handleException($e);
      }
    }

    $form_state->setRedirect("view.{$entity->getEntityTypeId()}.list", ['cloud_context' => $entity->getCloudContext()]);
  }

}
