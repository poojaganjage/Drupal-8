<?php

namespace Drupal\k8s\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\k8s\Service\K8sServiceException;

/**
 * Form controller for the Pod entity log forms.
 *
 * @ingroup k8s
 */
class K8sPodLogForm extends K8sContentForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    $entity = $this->entity;

    try {
      $this->k8sService->setCloudContext($entity->getCloudContext());
      $logs = $this->k8sService->getPodLogs($entity->getNamespace(), [
        'metadata' => ['name' => $entity->getName()],
      ]);
      $logs = htmlspecialchars($logs);
      $form['log'] = [
        '#type'          => 'item',
        '#markup'        => "<pre>$logs</pre>",
      ];
    }
    catch (K8sServiceException $e) {
      $this->messenger->addError($this->t('Unable to retrieve logs of @label.', [
        '@label' => $entity->getEntityType()->getSingularLabel(),
      ]));

      $this->logger('k8s')->error(t('Unable to retrieve logs of @label.', [
        '@label' => $entity->getEntityType()->getSingularLabel(),
      ]));
    }

    return $form;
  }

}
