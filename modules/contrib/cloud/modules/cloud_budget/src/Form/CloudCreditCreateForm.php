<?php

namespace Drupal\cloud_budget\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the cluster role create form.
 *
 * @ingroup cloud_budget
 */
class CloudCreditCreateForm extends CloudBudgetContentForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    $form = parent::buildForm($form, $form_state);

    $weight = -50;

    $form['cloud_budget'] = [
      '#type' => 'details',
      '#title' => $this->t('Credit'),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['cloud_budget']['user'] = $form['user'];
    unset($form['user']);

    $form['cloud_budget']['amount'] = $form['amount'];
    $form['cloud_budget']['amount']['widget'][0]['value']['#default_value'] =
      $this->configFactory
        ->get('cloud_budget.settings')
        ->get('credit_default_amount');
    unset($form['amount']);

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    $form['actions'] = $this->actions($form, $form_state, $cloud_context);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $user_input = $form_state->getValue('user');
    if (empty($user_input) || empty($user_input[0]) || empty($user_input[0]['target_id'])) {
      $form_state->setErrorByName('user', $this->t('The user is invalid.'));
      return;
    }

    $cloud_context = $this->routeMatch->getParameter('cloud_context');
    $users = \Drupal::entityTypeManager()
      ->getStorage($this->entity->getEntityTypeId())
      ->loadByProperties([
        'cloud_context' => $cloud_context,
        'user' => $user_input[0]['target_id'],
      ]);

    if (!empty($users)) {
      $form_state->setErrorByName('user', $this->t('The cloud credit of the user has existed.'));
    }

  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $entity->setRefreshed(time());
    $entity->save();

    $this->processOperationStatus($entity, 'created');

    $form_state->setRedirect('view.cloud_credit.list', ['cloud_context' => $entity->getCloudContext()]);
  }

}
