<?php

namespace Drupal\k8s\Form;

use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\k8s\Service\K8sService;
use Drupal\k8s\Service\K8sServiceException;

/**
 * Form controller for the Namespace entity create form.
 *
 * @ingroup k8s
 */
class K8sNamespaceCreateForm extends K8sContentForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    $form = parent::buildForm($form, $form_state);
    $this->k8sService->setCloudContext($cloud_context);

    try {
      // Try to test if getNamespaces() is successful or not.
      $this->k8sService->getNamespaces();
    }
    // If getNamespaces() is not successful, redirect to the namespace list.
    catch (\Exception $e) {
      $this->k8sService->handleError($e, $cloud_context, $this->entity);
    }

    $weight = -50;

    $form['namespace'] = [
      '#type' => 'details',
      '#title' => $this->t('Namespace'),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['namespace']['name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Name'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#required'      => TRUE,
    ];

    $form['namespace']['labels'] = $form['labels'];
    $form['namespace']['annotations'] = $form['annotations'];

    unset($form['labels']);
    unset($form['annotations']);

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    $form['actions'] = $this->actions($form, $form_state, $cloud_context);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->trimTextfields($form, $form_state);

    $cloud_context = $this->routeMatch->getParameter('cloud_context');

    $entity = $this->entity;
    $entity->setCloudContext($cloud_context);

    // Get labels.
    $labels = [];
    foreach ($entity->getLabels() ?: [] as $label) {
      $labels[$label['item_key']] = $label['item_value'];
    }

    $this->k8sService->setCloudContext($cloud_context);
    $params = [];
    $params['metadata']['name'] = $entity->getName();
    if (!empty($labels)) {
      $params['metadata']['labels'] = $labels;
    }

    try {

      $result = $this->k8sService->createNamespace($params);
      $entity->setStatus($result['status']['phase']);
      $entity->setCreated(strtotime($result['metadata']['creationTimestamp']));
      $entity->save();

      $this->processOperationStatus($entity, 'created');

      K8sService::clearCacheValue();

      $form_state->setRedirect('view.k8s_namespace.list', ['cloud_context' => $entity->getCloudContext()]);
    }
    catch (K8sServiceException
    | EntityStorageException
    | EntityMalformedException $e) {

      try {
        $this->processOperationErrorStatus($entity, 'created');
      }
      catch (EntityMalformedException $e) {
        $this->handleException($e);
      }
    }
  }

}
