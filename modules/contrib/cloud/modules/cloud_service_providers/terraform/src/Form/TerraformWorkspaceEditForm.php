<?php

namespace Drupal\terraform\Form;

use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\terraform\Service\TerraformServiceException;
use Drupal\terraform\Traits\TerraformFormTrait;

/**
 * Form controller for the entity edit form.
 *
 * @ingroup terraform
 */
class TerraformWorkspaceEditForm extends TerraformContentForm {

  use CloudContentEntityTrait;
  use TerraformFormTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    $form = parent::buildForm($form, $form_state);
    $this->terraformService->setCloudContext($cloud_context);
    $entity = $this->entity;

    $weight = -50;

    $form['workspace'] = [
      '#type' => 'details',
      '#title' => 'Workspace',
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['workspace']['name'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Name')),
      '#markup'        => $entity->getName(),
      '#weight'        => $weight++,
    ];

    $aws_cloud_options = $this->getAwsCloudOptions($cloud_context);
    if (!empty($aws_cloud_options)) {
      $form['workspace']['aws_cloud'] = [
        '#type'          => 'select',
        '#title'         => $this->t('AWS Cloud'),
        '#options'       => $aws_cloud_options,
        '#empty_value'   => '',
        '#default_value' => $entity->getAwsCloud(),
        '#weight'        => $weight++,
      ];
    }

    $form['vcs'] = [
      '#type' => 'details',
      '#title' => 'VCS',
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['vcs']['vcs_repo_identifier'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('VCS Repository')),
      '#markup'        => $entity->getVcsRepoIdentifier(),
      '#weight'        => $weight++,
    ];

    $form['vcs']['vcs_repo_branch'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('VCS Repository Branch'),
      '#description'   => $this->t('The repository branch that Terraform will execute from.'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#required'      => FALSE,
      '#default_value' => $entity->getVcsRepoBranch(),
      '#weight'        => $weight++,
    ];

    $form['vcs']['oauth_token_id'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('OAuth Token ID')),
      '#markup'        => $entity->getOauthTokenId(),
      '#weight'        => $weight++,
    ];

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    $form['actions'] = $this->actions($form, $form_state, $cloud_context);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Call copyFormItemValues() to ensure the form array is intact.
    $this->copyFormItemValues($form);

    $this->trimTextfields($form, $form_state);

    $entity = $this->entity;

    $cloud_context = $this->routeMatch->getParameter('cloud_context');
    $this->terraformService->setCloudContext($cloud_context);

    try {
      $entity->save();

      $params = [
        'workspace_id' => $entity->getWorkspaceId(),
        'vcs_repo_branch' => $entity->getVcsRepoBranch(),
      ];

      $this->terraformService->patchWorkspace($params);

      // Create variables for AWS Cloud if necessary.
      $this->updateAwsCloudVariables($entity);

      $this->processOperationStatus($entity, 'updated');
      $form_state->setRedirect('view.terraform_workspace.list', ['cloud_context' => $entity->getCloudContext()]);
    }
    catch (TerraformServiceException $e) {
      $this->processOperationErrorStatus($entity, 'updated');
    }
  }

}
