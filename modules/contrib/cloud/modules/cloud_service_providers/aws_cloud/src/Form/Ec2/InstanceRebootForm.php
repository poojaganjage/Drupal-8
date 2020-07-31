<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Form\FormStateInterface;

/**
 * Reboots an AWS Instance.
 */
class InstanceRebootForm extends AwsDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $entity = $this->entity;

    return $this->t('Are you sure you want to reboot instance: %name?', [
      '%name' => $entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Reboot');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $entity = $this->entity;

    $params = [
      'InstanceIds' => [
        $this->entity->getInstanceId(),
      ],
    ];

    $this->ec2Service->setCloudContext($this->entity->getCloudContext());
    $this->ec2Service->rebootInstances($params);

    $this->processOperationStatus($entity, 'rebooted');
    $this->clearCacheValues();

    $form_state->setRedirect("view.{$this->entity->getEntityTypeId()}.list", ['cloud_context' => $this->entity->getCloudContext()]);
  }

}
