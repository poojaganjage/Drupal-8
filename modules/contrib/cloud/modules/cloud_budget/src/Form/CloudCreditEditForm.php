<?php

namespace Drupal\cloud_budget\Form;

use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the cloud credit edit forms.
 *
 * @ingroup cloud_budget
 */
class CloudCreditEditForm extends CloudBudgetContentForm {

  use CloudContentEntityTrait;

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
    $form['cloud_budget']['user']['#disabled'] = TRUE;
    unset($form['user']);

    $form['cloud_budget']['amount'] = $form['amount'];
    unset($form['amount']);

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    $form['actions'] = $this->actions($form, $form_state, $cloud_context);

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $entity->save();

    $this->processOperationStatus($entity, 'updated');

    $form_state->setRedirect('view.cloud_credit.list', [
      'cloud_context' => $entity->getCloudContext(),
    ]);
  }

}
