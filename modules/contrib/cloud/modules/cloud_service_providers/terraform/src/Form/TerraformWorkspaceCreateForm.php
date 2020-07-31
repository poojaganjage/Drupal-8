<?php

namespace Drupal\terraform\Form;

use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\terraform\Entity\TerraformWorkspace;
use Drupal\terraform\Service\TerraformServiceException;
use Drupal\terraform\Traits\TerraformFormTrait;

/**
 * Form controller for the entity create form.
 *
 * @ingroup terraform
 */
class TerraformWorkspaceCreateForm extends TerraformContentForm {

  use CloudContentEntityTrait;
  use TerraformFormTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    $form = parent::buildForm($form, $form_state);
    $this->terraformService->setCloudContext($cloud_context);

    $weight = -50;

    $form['workspace'] = [
      '#type' => 'details',
      '#title' => 'Workspace',
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['workspace']['name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Name'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#required'      => TRUE,
      '#weight'        => $weight++,
    ];

    $aws_cloud_options = $this->getAwsCloudOptions($cloud_context);
    if (!empty($aws_cloud_options)) {
      $form['workspace']['aws_cloud'] = [
        '#type'          => 'select',
        '#title'         => $this->t('AWS Cloud'),
        '#options'       => $aws_cloud_options,
        '#empty_value'   => '',
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
      '#type'          => 'textfield',
      '#title'         => $this->t('VCS Repository'),
      '#description'   => $this->t('A reference to your VCS repository in the format :org/:repo where :org and :repo refer to the organization and repository in your VCS provider. For example, the value is "hashicorp/terraform" for the repository https://github.com/hashicorp/terraform.'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#required'      => TRUE,
      '#weight'        => $weight++,
    ];

    $form['vcs']['vcs_repo_branch'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('VCS Repository Branch'),
      '#description'   => $this->t("The repository branch that Terraform will execute from. If omitted or submitted as an empty string, this defaults to the repository's default branch (e.g. master). Tags are not supported."),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#required'      => FALSE,
      '#weight'        => $weight++,
    ];

    $form['vcs']['oauth_token_id'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('OAuth Token ID'),
      '#description'   => $this->t('The VCS Connection (OAuth Connection + Token) to use. About how to find it, please check the document <a href="@link" target="_blank">VCS Provider</a>.', [
        '@link' => 'https://www.terraform.io/docs/cloud/vcs/github.html#step-2-on-terraform-cloud-add-a-vcs-provider',
      ]),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#required'      => TRUE,
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
    $this->trimTextfields($form, $form_state);

    $entity = $this->entity;

    $cloud_context = $this->routeMatch->getParameter('cloud_context');
    $this->terraformService->setCloudContext($cloud_context);

    try {
      $params['name'] = $form_state->getValue('name');
      $params['vcs_repo_identifier'] = $form_state->getValue('vcs_repo_identifier');
      $params['oauth_token_id'] = $form_state->getValue('oauth_token_id');
      $params['vcs_repo_branch'] = $form_state->getValue('vcs_repo_branch');
      $result = $this->terraformService->createWorkspace($params);

      $entity->save();

      // Update the entity.
      $this->terraformService->updateWorkspaces([
        'name' => $entity->getName(),
      ], FALSE);

      // Reload entity to update workspace_id.
      $entity = TerraformWorkspace::load($entity->id());

      // Create variables for AWS Cloud if necessary.
      $this->updateAwsCloudVariables($entity);

      $this->processOperationStatus($entity, 'created');
      $form_state->setRedirect('view.terraform_workspace.list', ['cloud_context' => $entity->getCloudContext()]);
    }
    catch (TerraformServiceException $e) {
      $this->processOperationErrorStatus($entity, 'created');
    }
  }

}
