<?php

namespace Drupal\k8s\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\k8s\Service\K8sServiceException;

/**
 * Provides a form for deleting a Namespace entity.
 *
 * @ingroup k8s
 */
class K8sNamespaceDeleteForm extends K8sDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $entity = $this->entity;

    return $this->t('Are you sure you want to delete the "@name" namespace?<br>CAUTION: The role "@name" is also going to be deleted.', [
      '@name' => $entity->getName(),
    ]);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\k8s\Service\K8sServiceException
   *    Thrown when unable to delete entity.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->entity;

    try {
      $this->k8sService->setCloudContext($entity->getCloudContext());
      $this->k8sService->deleteNamespace([
        'metadata' => [
          'name' => $entity->getName(),
        ],
      ]);

      $entity->delete();

      $this->messenger->addStatus($this->getDeletionMessage());
      $this->logDeletionMessage();

      // Delete the role if it exists.
      $roles = $this->entityTypeManager->getStorage('user_role')
        ->loadByProperties([
          'id' => $entity->getName(),
        ]);
      if (!empty($roles)) {
        $role = reset($roles);
        $role->delete();
      }
    }
    catch (K8sServiceException $e) {

      $this->processOperationErrorStatus($entity, 'deleted');
    }

    $form_state->setRedirect('view.k8s_namespace.list', ['cloud_context' => $entity->getCloudContext()]);
  }

}
