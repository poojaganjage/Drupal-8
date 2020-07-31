<?php

namespace Drupal\vmware\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Stop a started VMware VM.
 *
 * @package Drupal\vmware\Form
 */
class VmwareVmStopForm extends VmwareDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $entity = $this->entity;

    return $this->t('Are you sure you want to stop VM: %name?', [
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

    try {
      $this->vmwareService->setCloudContext($entity->getCloudContext());
      $this->vmwareService->login();
      $this->vmwareService->stopVm([
        'VmId' => $entity->getVmId(),
      ]);

      $this->vmwareService->updateVms([
        'name' => $entity->getName(),
      ], FALSE);

      $this->processOperationStatus($entity, 'stopped');
      $this->clearCacheValues();
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }

    $form_state->setRedirect("view.{$entity->getEntityTypeId()}.list", ['cloud_context' => $entity->getCloudContext()]);
  }

}
