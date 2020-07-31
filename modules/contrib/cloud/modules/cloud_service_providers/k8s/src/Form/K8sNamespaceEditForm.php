<?php

namespace Drupal\k8s\Form;

use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\k8s\Service\K8sServiceException;

/**
 * Form controller for the Namespace entity edit forms.
 *
 * @ingroup k8s
 */
class K8sNamespaceEditForm extends K8sContentForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;
    $weight = -50;
    $form['namespace'] = [
      '#type' => 'details',
      '#title' => $this->t('Namespace'),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['namespace']['name'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Name')),
      '#markup'        => $entity->getName(),
    ];

    $form['namespace']['labels'] = $form['labels'];
    unset($form['labels']);

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    $form['actions'] = $this->actions($form, $form_state, $cloud_context);
    $form['actions']['#weight'] = $weight++;

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

    // Get labels.
    $labels = [];
    foreach ($entity->getLabels() ?: [] as $label) {
      $labels[$label['item_key']] = $label['item_value'];
    }

    $this->k8sService->setCloudContext($entity->getCloudContext());
    try {
      $this->k8sService->updateNamespace([
        'metadata' => [
          'name' => $entity->getName(),
          'labels' => $labels,
        ],
      ]);

      $entity->save();

      $this->processOperationStatus($entity, 'updated');
    }
    catch (K8sServiceException
    | EntityStorageException
    | EntityMalformedException $e) {

      try {
        $this->processOperationErrorStatus($entity, 'updated');
      }
      catch (EntityMalformedException $e) {
        $this->handleException($e);
      }
    }

    $form_state->setRedirect('view.k8s_namespace.list', [
      'cloud_context' => $entity->getCloudContext(),
    ]);
  }

}
