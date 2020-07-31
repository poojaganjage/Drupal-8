<?php

namespace Drupal\k8s\Form;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\k8s\Entity\K8sEntityBase;
use Drupal\k8s\Service\K8sServiceException;

/**
 * Form controller for the entity edit forms.
 *
 * @ingroup k8s
 */
class K8sEditForm extends K8sContentForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    $name_underscore = $this->getShortEntityTypeNameUnderscore($entity);
    $name_whitespace = $this->getShortEntityTypeNameWhitespace($entity);

    $weight = -50;
    $form[$name_underscore] = [
      '#type' => 'details',
      '#title' => $name_whitespace,
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form[$name_underscore]['name'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Name')),
      '#markup'        => $entity->getName(),
      '#weight'        => $weight++,
    ];

    if (method_exists($this->entity, 'getNamespace')) {
      $form[$name_underscore]['namespace'] = [
        '#type'          => 'item',
        '#title'         => $this->getItemTitle($this->t('Namespace')),
        '#markup'        => $entity->getNamespace(),
        '#weight'        => $weight++,
      ];
    }

    $form[$name_underscore]['detail'] = $form['detail'];
    unset($form['detail']);

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    $form['actions'] = $this->actions($form, $form_state, $cloud_context);
    $form['actions']['#weight'] = $weight++;

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\k8s\Service\K8sServiceException
   *    Thrown when unable to update entity.
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Call copyFormItemValues() to ensure the form array is intact.
    $this->copyFormItemValues($form);

    $this->trimTextfields($form, $form_state);
    $entity = $this->entity;

    $name_underscore = $this->getShortEntityTypeNameUnderscore($entity);
    $name_camel = $this->getShortEntityTypeNameCamel($entity);

    $this->k8sService->setCloudContext($entity->getCloudContext());
    try {
      $method_name = "update{$name_camel}";

      $params = Yaml::decode($entity->getDetail());

      if (!empty($entity->getOwner())) {
        // Add owner uid to annotations.
        $params['metadata']['annotations'][K8sEntityBase::ANNOTATION_CREATED_BY_UID] = $entity->getOwner()->id();
      }

      if (method_exists($this->entity, 'getNamespace')) {
        $this->k8sService->$method_name(
          $entity->getNamespace(),
          $params
        );
      }
      else {
        $this->k8sService->$method_name($params);
      }

      $entity->save();

      // Update the entity.
      $name_plural_camel = $this->getShortEntityTypeNamePluralCamel($entity);
      $method_name = "update${name_plural_camel}";
      $this->k8sService->$method_name([
        'metadata.name' => $entity->getName(),
      ], FALSE);

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

    $form_state->setRedirect("view.k8s_{$name_underscore}.list", [
      'cloud_context' => $entity->getCloudContext(),
    ]);
  }

}
