<?php

namespace Drupal\terraform\Form;

use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\terraform\Entity\TerraformWorkspace;
use Drupal\terraform\Service\TerraformServiceException;

/**
 * Form controller for the entity edit form.
 *
 * @ingroup terraform
 */
class TerraformVariableEditForm extends TerraformContentForm {

  use CloudContentEntityTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    $form = parent::buildForm($form, $form_state);
    $this->terraformService->setCloudContext($cloud_context);
    $entity = $this->entity;

    $weight = -50;

    $form['variable'] = [
      '#type' => 'details',
      '#title' => 'Variable',
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['variable']['attribute_key'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Key'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#required'      => TRUE,
      '#default_value' => $entity->getAttributeKey(),
      '#weight'        => $weight++,
    ];

    $type = 'textfield';
    if ($entity->getAttributeKey() === 'AWS_SECRET_ACCESS_KEY') {
      $type = 'password';
    }
    $form['variable']['attribute_value'] = [
      '#type'          => $type,
      '#title'         => $this->t('Value'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#required'      => TRUE,
      '#weight'        => $weight++,
    ];
    if ($type === 'textfield') {
      $form['variable']['attribute_value']['#default_value'] = $entity->getAttributeValue();
    }

    $form['variable']['description'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Description'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => $entity->getDescription(),
      '#weight'        => $weight++,
    ];

    $form['variable']['category'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Category'),
      '#options'       => [
        'terraform'    => $this->t('Terraform Variable'),
        'env'          => $this->t('Environment Variable'),
      ],
      '#required'      => TRUE,
      '#default_value' => $entity->getCategory(),
      '#weight'        => $weight++,
    ];

    $form['variable']['hcl'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('HCL'),
      '#description'   => $this->t('Parse this field as HashiCorp Configuration Language (HCL). This allows you to interpolate values at runtime.'),
      '#default_value' => $entity->getHcl(),
      '#weight'        => $weight++,
    ];

    $form['variable']['sensitive'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Sensitive'),
      '#description'   => $this->t('Sensitive variables are never shown in the UI or API. They may appear in Terraform logs if your configuration is designed to output them.'),
      '#default_value' => $entity->getSensitive(),
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

      $params = [
        'workspace_id' => $terraform_workspace->getWorkspaceId(),
        'variable_id' => $entity->getVariableId(),
        'key' => $form_state->getValue('attribute_key'),
        'value' => $form_state->getValue('attribute_value'),
        'description' => $form_state->getValue('description'),
        'category' => $form_state->getValue('category'),
        'hcl' => !empty($form_state->getValue('hcl')),
        'sensitive' => !empty($form_state->getValue('sensitive')),
      ];

      $result = $this->terraformService->patchVariable($params);

      // Update the entity.
      $this->terraformService->updateVariables([
        'terraform_workspace' => $terraform_workspace,
        'name' => $entity->getName(),
      ], FALSE);

      $this->processOperationStatus($entity, 'updated');

      $form_state->setRedirect("view.terraform_variable.list", [
        'cloud_context' => $entity->getCloudContext(),
        'terraform_workspace' => $terraform_workspace_id,
      ]);
    }
    catch (TerraformServiceException $e) {
      $this->processOperationErrorStatus($entity, 'updated');
    }
  }

}
