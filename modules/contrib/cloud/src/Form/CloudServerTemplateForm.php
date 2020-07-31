<?php

namespace Drupal\cloud\Form;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for cloud server template edit forms.
 *
 * @ingroup cloud_server_template
 */
class CloudServerTemplateForm extends CloudContentForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    /* @var $entity \Drupal\cloud\Entity\CloudServerTemplate */
    $form = parent::buildForm($form, $form_state);

    $weight = -50;

    if (!$this->entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => $weight++,
      ];
    }

    $entity = $this->entity;

    // Setup the cloud_context based on value passed in the path.
    $form['cloud_context']['#disabled'] = TRUE;
    if ($entity->isNew()) {
      $form['cloud_context']['widget'][0]['value']['#default_value'] = $cloud_context;
    }

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') !== FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime(\Drupal::time()->getRequestTime());
      $entity->setRevisionUserId($this->currentUser()->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    $status = parent::save($form, $form_state);
    $this->messenger->deleteAll();
    switch ($status) {
      case SAVED_NEW:
        $this->processOperationStatus($entity, 'created');
        break;

      default:
        $this->processOperationStatus($entity, 'updated');
    }
    $form_state->setRedirect('entity.cloud_server_template.canonical', ['cloud_server_template' => $entity->id(), 'cloud_context' => $entity->getCloudContext()]);

    // Clear block and menu cache.
    CloudContentEntityBase::updateCache();
  }

}
