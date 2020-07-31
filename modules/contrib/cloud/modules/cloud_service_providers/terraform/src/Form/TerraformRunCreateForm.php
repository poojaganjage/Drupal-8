<?php

namespace Drupal\terraform\Form;

use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\terraform\Entity\TerraformWorkspace;
use Drupal\terraform\Service\TerraformServiceException;

/**
 * Form controller for the entity create form.
 *
 * @ingroup terraform
 */
class TerraformRunCreateForm extends TerraformContentForm {

  use CloudContentEntityTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    $form = parent::buildForm($form, $form_state);
    $this->terraformService->setCloudContext($cloud_context);

    $weight = -50;

    $form['run'] = [
      '#type' => 'details',
      '#title' => 'Run',
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['run']['message'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Message'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#required'      => TRUE,
      '#default_value' => 'Queued manually in Cloud Orchestrator',
      '#weight'        => $weight++,
    ];

    $form['run']['is_destroy'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Destroy'),
      '#description'   => $this->t('Specifies if this is a destroy plan, which will destroy all provisioned resources.'),
      '#weight'        => $weight++,
    ];

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    $form['actions'] = $this->actions($form, $form_state, $cloud_context);

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\terraform\Service\TerraformServiceException
   *    Thrown when unable to create entity.
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->trimTextfields($form, $form_state);

    $entity = $this->entity;

    $cloud_context = $this->routeMatch->getParameter('cloud_context');
    $this->terraformService->setCloudContext($cloud_context);

    try {
      $terraform_workspace_id = $this->getRouteMatch()->getParameter('terraform_workspace');
      $terraform_workspace = TerraformWorkspace::load($terraform_workspace_id);
      $message = $form_state->getValue('message');
      if ($form_state->getValue('is_destroy')) {
        $message .= ' (destroy)';
      }
      $params = [
        'message' => $message,
        'workspace_id' => $terraform_workspace->getWorkspaceId(),
        'is_destroy' => !empty($form_state->getValue('is_destroy')),
      ];

      $result = $this->terraformService->createRun($params);
      $entity->setName($result['id']);
      $entity->setTerraformWorkspaceId($terraform_workspace_id);
      $entity->save();

      // Update the entity.
      $this->terraformService->updateRuns([
        'terraform_workspace' => $terraform_workspace,
        'name' => $entity->getName(),
      ], FALSE);

      $this->processOperationStatus($entity, 'created');

      $form_state->setRedirect("view.terraform_run.list", [
        'cloud_context' => $entity->getCloudContext(),
        'terraform_workspace' => $terraform_workspace_id,
      ]);
    }
    catch (TerraformServiceException $e) {
      $this->processOperationErrorStatus($entity, 'created');
    }
  }

}
