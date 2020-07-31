<?php

namespace Drupal\terraform\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\terraform\Entity\TerraformWorkspace;
use Drupal\terraform\Service\TerraformServiceException;

/**
 * Apply Run form.
 */
class TerraformRunApplyForm extends TerraformDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    /* @var \Drupal\aws_cloud\Entity\Ec2\ElasticIp $entity */
    $entity = $this->entity;
    return $this->t('Are you sure you want to apply this Run (@run_id)', [
      '@run_id' => $entity->getRunId(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Apply');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->terraformService->setCloudContext($this->entity->getCloudContext());
    $entity = $this->entity;
    try {
      $result = $this->terraformService->applyRun([
        'terraform_run' => $entity,
      ]);

      // Update the entity.
      $terraform_workspace_id = $entity->getTerraformWorkspaceId();
      $terraform_workspace = TerraformWorkspace::load($terraform_workspace_id);
      $this->terraformService->updateRuns([
        'terraform_workspace' => $terraform_workspace,
        'name' => $entity->getName(),
      ], FALSE);
    }
    catch (TerraformServiceException $e) {

      $this->processOperationErrorStatus($entity, 'applied');
    }

    $form_state->setRedirect("view.{$entity->getEntityTypeId()}.list", [
      'cloud_context' => $entity->getCloudContext(),
      'terraform_workspace' => $this->getRouteMatch()->getParameter('terraform_workspace'),
    ]);
  }

}
