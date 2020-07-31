<?php

namespace Drupal\k8s\Form;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\k8s\Entity\K8sEntityBase;
use Drupal\k8s\Service\K8sServiceException;

/**
 * Form controller for the entity create form.
 *
 * @ingroup k8s
 */
class K8sCreateForm extends K8sContentForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    $form = parent::buildForm($form, $form_state);
    $this->k8sService->setCloudContext($cloud_context);

    $name_underscore = $this->getShortEntityTypeNameUnderscore($this->entity);
    $name_whitespace = $this->getShortEntityTypeNameWhitespace($this->entity);

    $weight = -50;

    $form[$name_underscore] = [
      '#type' => 'details',
      '#title' => $name_whitespace,
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $options = [];
    try {
      $options = $this->getNamespaceOptions();
    }
    catch (\Exception $e) {
      $this->k8sService->handleError($e, $cloud_context, $this->entity);
    }

    if (!empty($options)
    && method_exists($this->entity, 'getNamespace')) {

      $form[$name_underscore]['namespace'] = [
        '#type'          => 'select',
        '#title'         => $this->t('Namespace'),
        '#options'       => $options,
        '#default_value' => 'default',
        '#required'      => TRUE,
      ];
    }

    $form[$name_underscore]['detail'] = $form['detail'];
    unset($form['detail']);

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    $form['actions'] = $this->actions($form, $form_state, $cloud_context);

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\k8s\Service\K8sServiceException
   *    Thrown when unable to create entity.
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->trimTextfields($form, $form_state);

    $entity = $this->entity;

    $cloud_context = $this->routeMatch->getParameter('cloud_context');
    $this->k8sService->setCloudContext($cloud_context);

    $name_underscore = $this->getShortEntityTypeNameUnderscore($entity);
    $name_camel = $this->getShortEntityTypeNameCamel($entity);

    $params = Yaml::decode($entity->getDetail());

    if (!empty($entity->getOwner())) {
      // Add owner uid to annotations.
      $params['metadata']['annotations'][K8sEntityBase::ANNOTATION_CREATED_BY_UID] = $entity->getOwner()->id();
    }

    try {
      $method_name = "create{$name_camel}";
      if (method_exists($this->entity, 'getNamespace')) {
        $result = $this->k8sService->$method_name($entity->getNamespace(), $params);
      }
      else {
        $result = $this->k8sService->$method_name($params);
      }

      $entity->setName($result['metadata']['name']);
      if (method_exists($entity, 'setCreationYaml')) {
        $entity->setCreationYaml($entity->getDetail());
      }
      $entity->save();

      // Update the entity.
      $name_plural_camel = $this->getShortEntityTypeNamePluralCamel($entity);
      $method_name = "update${name_plural_camel}";
      $this->k8sService->$method_name([
        'metadata.name' => $entity->getName(),
      ], FALSE);

      $this->processOperationStatus($entity, 'created');

      $form_state->setRedirect("view.k8s_{$name_underscore}.list", ['cloud_context' => $entity->getCloudContext()]);
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
