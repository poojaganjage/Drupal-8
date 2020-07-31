<?php

namespace Drupal\k8s\Form;

use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\k8s\Service\K8sServiceException;

/**
 * Form controller for the deployment scale forms.
 *
 * @ingroup k8s
 */
class K8sDeploymentScaleForm extends K8sDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $entity = $this->entity;

    return $this->t('Scale the deployment: %name?', [
      '%name' => $entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Scale');
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
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    // Update the entity to the latest state.
    $this->k8sService->setCloudContext($entity->getCloudContext());
    $this->k8sService->updateDeployments([
      'metadata.namespace' => $entity->getNamespace(),
      'metadata.name' => $entity->getName(),
    ], FALSE);

    $weight = -50;
    $form['description'] = [
      '#markup'         => "Current status: {$entity->getReadyReplicas()} created.",
      '#weight'         => $weight++,
    ];

    $form['pod_number'] = [
      '#type'           => 'number',
      '#title'          => $this->t('Desire number of pods'),
      '#required'       => TRUE,
      '#default_value'  => $entity->getReplicas(),
      '#min'            => 1,
      '#weight'         => $weight++,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $this->k8sService->setCloudContext($entity->getCloudContext());
    try {
      $params = Yaml::decode($entity->getDetail());
      $params['spec']['replicas'] = (int) $form_state->getValue('pod_number');

      $this->k8sService->updateDeployment(
        $entity->getNamespace(),
        $params
      );
      $entity->save();

      // Update the entity.
      $this->k8sService->updateDeployments([
        'metadata.namespace' => $entity->getNamespace(),
        'metadata.name' => $entity->getName(),
      ], FALSE);

      $this->processOperationStatus($entity, 'scaled');
    }
    catch (K8sServiceException $e) {

      try {
        $this->processOperationErrorStatus($entity, 'scaled');
      }
      catch (EntityMalformedException $e) {
        $this->handleException($e);
      }
    }

    $form_state->setRedirect('view.k8s_deployment.list', [
      'cloud_context' => $entity->getCloudContext(),
    ]);
  }

}
